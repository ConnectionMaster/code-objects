<?php
/**
 * ImplementationResolver.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Resolver;

/**
 * Extends ClassResolver to resolve only classes that implement the given interface.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Resolver
 */
class ImplementationResolver extends ClassResolver
{
  /** @var string */
  protected $interface;

  /**
   * @param string   $interface the interface that resolved classes must implement
   * @param string[] $siblings  optional array of related fully qualified class names to be used to resolve the class
   */
  final public function __construct($interface, $siblings = [], $projectDir = null)
  {
    if (interface_exists($interface))
    {
      $this->interface = $interface;

      foreach ($siblings as $class)
      {
        if (class_exists($class) && $this->isImplementation($class))
        {
          throw new \LogicException(sprintf('The given class "%s" does not implement "%s"', $class, $this->interface));
        }
      }

      parent::__construct($siblings, $projectDir);
    }
    else
    {
      throw new \LogicException(sprintf('The given string "%s" is not a fully qualified interface name.', $interface));
    }
  }

  // region //////////////////////////////////////////////// Public Methods

  /**
   * Returns the fully qualified interface name for the interface that this class will resolve classes for.
   *
   * @return string
   */
  public function getInterface()
  {
    return $this->interface;
  }

  /**
   * Resolves the string in to a full qualified class name, and ensures that the class implements the proper interface.
   *
   * @param string $name The class name as a non-fully-qualified string
   *
   * @return string|null the fully qualified class name, or null if it couldn't be resolved
   *
   * @throws \LogicException if the class cannot be resolved or does not implement the proper interface
   */
  public function resolve($name)
  {
    if ($class = parent::resolve($name))
    {
      if ($this->isImplementation($class))
      {
        return $class;
      }

      throw new \LogicException(sprintf('The class "%s" resolved by the ClassResolver does not implement "%s"', $class, $this->getInterface()));
    }

    return null;
  }

  // endregion ///////////////////////////////////////////// Public Methods

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * @param $class
   *
   * @return bool
   */
  protected function isImplementation($class)
  {
    return is_a($class, $this->getInterface(), true);
  }

  // endregion ///////////////////////////////////////////// Helper Methods
}
