<?php
/**
 * MethodString.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Object;

/**
 * Object for a string that represents a class method in the format of ClassName::method.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 *
 * @package DevCoding\CodeObject\Object
 */
class MethodString extends ConstructString
{
  /** @var ClassString */
  protected $class;
  /** @var ConstructString */
  protected $method;

  // region //////////////////////////////////////////////// Public Methods

  /**
   * Returns the class name only as a ClassString object
   *
   * @return ClassString|null The ClassString, or null if not present
   */
  public function getClass()
  {
    if (!isset($this->class))
    {
      if ($method = $this->getName())
      {
        $this->class = new ClassString(str_replace('::'.$method, '', $this->string));
      }
    }

    return $this->class;
  }

  /**
   * Returns the method name only as a ConstructString object.
   *
   * @return ConstructString The method as a ConstructString
   */
  public function getName()
  {
    if (!isset($this->method))
    {
      $parts = explode('::', $this->string);

      $this->method = new ConstructString($parts[1] ?? $parts[0]);
    }

    return $this->method;
  }

  /**
   * Evaluates whether this string, in its original form, is callable.
   *
   * @return bool TRUE if callable, FALSE if not
   */
  public function isCallable()
  {
    return is_callable($this->string);
  }

  /**
   * Returns an array representing this method.
   *
   * @return array|null array in the format of [ClassName, MethodName], or null if not parsable
   */
  public function toArray()
  {
    if ($method = $this->getName())
    {
      if ($class = $this->getClass())
      {
        return [$class, $method];
      }
      else
      {
        return ['\\', $method];
      }
    }

    return null;
  }

  // endregion ///////////////////////////////////////////// Public Methods
}
