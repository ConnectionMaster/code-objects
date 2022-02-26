<?php
/**
 * Closure.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Resolver;

use DevCoding\CodeObject\Exception\BadCallableException;
use DevCoding\CodeObject\Object\MethodString;

/**
 * Using the fromCallable method, creates a closure from a callable or invokable, instantiating objects as possible and
 * needed.  In PHP 7.1+, uses the native \Closure::fromCallable for the final steps.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object
 */
class Closure
{
  // region //////////////////////////////////////////////// Public Methods

  /**
   * Creates a closure from the given callable or invokable. If possible, uses PHP7.1's native \Closure::fromCallable.
   *
   * @param callable|string $callable a callable or fully qualified class name to an invokable class
   *
   * @see   \Closure::fromCallable()
   * @return \Closure the closure created from the callable
   * @throws BadCallableException
   */
  public static function fromCallable($callable)
  {
    if (!$callable instanceof \Closure)
    {
      $type = gettype($callable);
      if (!is_callable($callable))
      {
        try
        {
          $callable = static::normalize($callable);
        }
        catch (BadCallableException $e)
        {
          throw static::exception('The argument should be a callable, but was a %s.', $type, $e);
        }
      }

      // In case we've got it native, let's use that native one!
      if (method_exists(\Closure::class, 'fromCallable'))
      {
        /* @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
        return \Closure::fromCallable($callable);
      }
      else
      {
        return function () use ($callable) {
          return call_user_func_array($callable, func_get_args());
        };
      }
    }

    return $callable;
  }

  // endregion ///////////////////////////////////////////// Public Methods

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * Normalizes a string or array into a PHP callable using the normalizeClass, normalizeString, or
   * normalizeArray methods.
   *
   * @param string|array|object $callable
   *
   * @return callable
   * @throws BadCallableException
   */
  protected static function normalize($callable)
  {
    if (!is_callable($callable) && !is_object($callable))
    {
      if (class_exists($callable))
      {
        $callable = static::normalizeClass($callable);
      }
      elseif (is_string($callable))
      {
        $callable = static::normalizeString($callable);
      }
      elseif (is_array($callable))
      {
        $callable = static::normalizeArray($callable);
      }
    }

    return $callable;
  }

  /**
   * Normalizes a [ClassName, MethodName] array into a callable, instantiating the class if possible and necessary.
   *
   * @param array $array
   *
   * @return array|callable
   * @throws BadCallableException
   */
  protected static function normalizeArray($array)
  {
    if (!is_callable($array))
    {
      try
      {
        $rc = new \ReflectionClass($array[0]);
        if (0 === $rc->getConstructor()->getNumberOfRequiredParameters())
        {
          if ($rc->hasMethod($array[1]))
          {
            return [$rc->newInstance(), $array[1]];
          }
          else
          {
            throw static::exception('The method in the callable "[%s, %s]" does not exist in the callee class.', $array);
          }
        }
        else
        {
          throw static::exception('The callee class of the callable "[%s, %s]" is a string class with required arguments.', $array);
        }
      }
      catch (\ReflectionException $e)
      {
        throw static::exception('The callee class of the callable "[%s, %s]" does not exist, or could not be examined.', $array, $e);
      }
    }

    return $array;
  }

  /**
   * Evaluates if the given class is invokable, then instantiates the class.  If given a non-invokable class, throws
   * a BadCallableException.
   *
   * @param string $string
   *
   * @return callable
   * @throws BadCallableException
   */
  protected static function normalizeClass(string $string)
  {
    try
    {
      $rc = new \ReflectionClass($string);
      if ($rc->hasMethod('__invoke'))
      {
        return new $string();
      }

      throw static::exception('The argument "%s" was an existing class, but that class is not Invokable and no method was provided.', $string);
    }
    catch (\ReflectionException $e)
    {
      throw static::exception('The argument class "%" does not exist, or could not be examined.', $string, $e );
    }
  }

  /**
   * Normalizes a ClassName::methodName string into a [ClassName, MethodName] callable, instantiating the class if
   * needed and possible.
   *
   * @param string $string
   *
   * @return callable
   * @throws BadCallableException
   */
  protected static function normalizeString(string $string)
  {
    if (!is_callable($string))
    {
      // Break it down into parts
      $MethodString = new MethodString($string);
      if ($method = $MethodString->getName())
      {
        if ($class = $MethodString->getClass())
        {
          try
          {
            return static::normalizeArray([$class, $method]);
          }
          catch (BadCallableException $e)
          {
            throw static::exception('The argument "%s" could not be resolved into a callable.', $string, $e);
          }
        }
      }

      throw static::exception(sprintf('The argument "%s" could not be broken down into an existing class and method.', $string));
    }

    return $string;
  }

  /**
   * Creates a BadCallableException using the given template and arguments.
   *
   * @param string            $template
   * @param string|array|null $args
   * @param \Throwable|null   $previous
   *
   * @return BadCallableException
   */
  private static function exception($template, $args = null, $previous = null)
  {
    $args = is_array($args) ? $args : [$args];

    return new BadCallableException(vsprintf($template, $args), 0, $previous);
  }

  // endregion ///////////////////////////////////////////// Helper Methods
}
