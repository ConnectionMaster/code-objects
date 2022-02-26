<?php
/**
 * CallbackResolver.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Resolver;

use DevCoding\CodeObject\Exception\BadCallableException;
use DevCoding\CodeObject\Exception\BadClassException;
use DevCoding\CodeObject\Resolver\Closure;
use DevCoding\CodeObject\Object\MethodString;

/**
 * Resolver containing methods to resolve a given string, array, callable, object or \Closure into a callable.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 *
 * @package DevCoding\CodeObject\Resolver
 */
class CallbackResolver
{
  /** @var string[]|object[] */
  protected $namespaces;
  /** @var string|null */
  protected $projectDir;

  /**
   * @param string[]|object[] $namespaces_or_siblings Namespaces or siblings to resolve non-fully qualified classes
   * @param string            $projectDir             The root directory of the project or application
   */
  public function __construct($namespaces_or_siblings, $projectDir = null)
  {
    $this->projectDir = $projectDir;
    $this->namespaces = $namespaces_or_siblings;
  }

  // region //////////////////////////////////////////////// Public Methods

  /**
   * Normalizes the given array, string, callable, or object, resolving classes into fully qualified class names, fully
   * qualified class names into objects if possible and necessary, strings into [Class,Method] arrays.
   *
   * @param array|callable|string|object $callback The intended callable to normalize
   *
   * @return array|callable|string|object The normalized data
   */
  public function normalize($callback)
  {
    if (is_string($callback))
    {
      return $this->normalizeString($callback);
    }
    elseif (is_array($callback))
    {
      return $this->normalizeArray($callback);
    }

    return $callback;
  }

  /**
   * Resolves the given variable into a \Closure if possible, or throws a BadCallableException.
   *
   * @param string|array|callable|object|\Closure $callback the callback to resolve
   *
   * @return \Closure The resolved callable
   *
   * @throws BadCallableException When the callable cannot be resolved.  Typically, contains a detailed message
   */
  public function resolve($callback)
  {
    try
    {
      return Closure::fromCallable($callback);
    }
    catch (BadCallableException $badCallable)
    {
      try
      {
        $callback = $this->normalize($callback);
      }
      catch (BadClassException $badClass)
      {
        throw new BadCallableException($badCallable->getMessage(), 0, $badClass);
      }

      return Closure::fromCallable($callback);
    }
  }

  // endregion ///////////////////////////////////////////// Public Methods

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * Resolves the first element of the given array into a fully qualified class name if possible.
   *
   * @param array $array The intended callable array to normalize
   *
   * @return array The normalized data
   */
  protected function normalizeArray(array $array)
  {
    $class = array_shift($array);

    if (!is_object($class))
    {
      if (!class_exists($class))
      {
        if ($class = (new ClassResolver($this->namespaces))->resolve($class))
        {
          array_unshift($array, $class);

          return $array;
        }
      }
    }

    return $array;
  }

  /**
   * Resolves the class portion of a string intended to be a callable.
   *
   * @param string $string The intended callable string to normalize
   *
   * @return string The normalized data
   */
  protected function normalizeString(string $string)
  {
    if (!is_callable($string))
    {
      // Break it down into parts
      $MethodString = new MethodString($string);
      if ($MethodString->getName())
      {
        if ($class = $MethodString->getClass())
        {
          if (!class_exists($class))
          {
            return str_replace($class, (new ClassResolver($this->namespaces))->resolve($class), $string);
          }
        }
      }
      elseif (!class_exists($string))
      {
        return (new ClassResolver($this->namespaces))->resolve($string);
      }
    }

    return $string;
  }

  // endregion ///////////////////////////////////////////// Helper Methods
}
