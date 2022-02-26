<?php
/**
 * ContainerAwareCallbackResolver.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Resolver;

use DevCoding\CodeObject\Exception\BadCallableException;
use DevCoding\CodeObject\Object\MethodString;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Extends CallbackResolver to include resolution of services from a PSR-11 container.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 *
 * @package DevCoding\CodeObject\Resolver
 */
class ContainerAwareCallbackResolver extends CallbackResolver
{
  /** @var PsrContainerInterface */
  protected $container;

  /**
   * @param PsrContainerInterface $container              a PSR-11 container containing service instances
   * @param string[]|object[]     $namespaces_or_siblings Namespaces or siblings to resolve non-fully qualified classes
   * @param string                $projectDir             The root directory of the project or application
   */
  public function __construct(PsrContainerInterface $container, $namespaces_or_siblings = [], $projectDir = null)
  {
    $this->container = $container;

    parent::__construct($namespaces_or_siblings, $projectDir);
  }

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * Evaluates whether the given string has an @ prefix, indicating that it is a service ID.
   *
   * @param string $id the string to evaluate
   *
   * @return bool TRUE if the string is prefixed with @, FALSE otherwise
   */
  protected function isAtPrefixed($id)
  {
    return '@' === substr($id, 0, 1);
  }

  /**
   * Override to allow for the resolution of string IDs of service instances from this object's container.  A service
   * ID may be the first element in the given array.
   *
   * @param array $array the potential callable array to resolve
   *
   * @return array A callable array with an object and string, or class and string
   *
   * @throws BadCallableException If the array contains a service ID which cannot be retrieved or resolved
   */
  protected function normalizeArray(array $array)
  {
    $callee = array_shift($array);
    $method = !empty($array) ? array_shift($array) : null;
    $tmpl   = 'Service "%s" from callable "[%s, %s]"';

    try
    {
      return $this->normalizeService($callee, $method);
    }
    catch (NotFoundExceptionInterface $e)
    {
      if (!$this->isAtPrefixed($callee))
      {
        // The callee wasn't found as a service id.  Treat it as a class unless it has @ prefix.
        return parent::normalizeArray($array);
      }

      throw new BadCallableException(sprintf($tmpl.' was not found.', $callee, $callee, $method), 0, $e);
    }
    catch (ContainerExceptionInterface $e)
    {
      if (!$this->isAtPrefixed($callee))
      {
        try
        {
          // The container malfunctioned, but the callee doesn't have an @ prefix.  Try to treat it as a class
          return parent::normalizeArray($array);
        }
        catch (BadCallableException $bce)
        {
          // That went so well! Right. Don't do anything here, let the exception fly below
          // using the original exception parent as that's likely the real problem.
        }
      }

      // The container malfunctioned and the callee has an @ prefix implying we need the container to resolve
      $m = sprintf($tmpl.' could not be retrieved from the container.', $callee, $callee, $method);

      throw new BadCallableException($m, 0, $e);
    }
    catch (\ReflectionException $e)
    {
      // Something had already gone wrong, so we tried to use Reflection to discover what, and that went wrong too.
      $m = sprintf($tmpl.' exists, but the method is not callable.', $callee, $callee, $method);

      throw new BadCallableException($m, 0, $e);
    }
  }

  /**
   * Normalizes the given service ID into an object using the service locator container of this object.
   *
   * @return object|array the object or service to resolve
   *
   * @throws \BadMethodCallException     if the service exists but doesn't contain the given method
   * @throws \ReflectionException        if the service exists but the object cannot be analyzed
   * @throws NotFoundExceptionInterface  if the service doesn't exist
   * @throws ContainerExceptionInterface if there is another problem with the container
   */
  protected function normalizeService($id, $method = null)
  {
    // Remove the @ if needed
    $id = $this->isAtPrefixed($id) ? substr($id, 1) : $id;
    // Get the service object.  This may throw an exception, which we want to bubble up.
    $object = $this->container->get($id);

    if ($method)
    {
      if (is_callable([$object, $method]) && method_exists($object, $method))
      {
        // We test for both because __call and __callStatic fool is_callable
        return [$object, $method];
      }
      else
      {
        $cl = get_class($object);
        $ca = sprintf('[%s, %s]', $cl, $method);
        $cs = sprintf('%s::%s', $cl, $method);
        if (method_exists($object, $method))
        {
          $rClass  = new \ReflectionClass($cl);
          $rMethod = $rClass->getMethod($method);

          if ($rMethod->isPrivate() || $rMethod->isProtected())
          {
            $msg = sprintf('The service "%s" exists, but the method "%s" does not have public visibility', $id, $cs);
          }
          else
          {
            // This isn't possible: if a method is public in an existing object, it should be callable. We go for it..
            return [$object, $method];
          }
        }
        else
        {
          $msg = sprintf('The callable "%s" is invalid. The method "%" does not exist.', $ca, $cs);
        }

        throw new \BadMethodCallException($msg);
      }
    }
    else
    {
      // Just return the object and let the calling method deal with further normalization & validation.
      return $object;
    }
  }

  /**
   * Override to allow the resolution of string IDs for service instances from this object's service locator container.
   *
   * @param string $string The intended callable string to normalize
   *
   * @return string|array The normalized data
   *
   * @throws BadCallableException If the service container is unable to provide services
   */
  protected function normalizeString(string $string)
  {
    $MethodString = new MethodString($string);
    // Get the Callee and Method
    $method = $MethodString->getName() ?: null;
    $callee = $method ? $MethodString->getClass() : $string;
    $tmpl   = 'The service "%s" of callable "%s"';

    try
    {
      return $this->normalizeService($callee, $method);
    }
    catch (NotFoundExceptionInterface $e)
    {
      // The callee was not found as a service, so we're going to proceed under the assumption that it's not a service.
      return parent::normalizeString($string);
    }
    catch (ContainerExceptionInterface $e)
    {
      // Something went wrong in the container, we're just going to throw an error to not muddy the waters.
      throw new BadCallableException(sprintf($tmpl.' could not be retrieved from the container.', $callee, $method));
    }
    catch (\ReflectionException $e)
    {
      // Something had already gone wrong, so we tried to use Reflection to discover what, and that went wrong too.
      $m = sprintf($tmpl.' exists, but the method is not callable.', $callee, $callee, $method);

      throw new BadCallableException($m, 0, $e);
    }
  }

  // endregion ///////////////////////////////////////////// Helper Methods
}
