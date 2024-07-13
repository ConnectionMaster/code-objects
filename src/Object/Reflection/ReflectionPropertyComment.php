<?php

namespace DevCoding\CodeObject\Object\Reflection;

/**
 * Reflection-style class designed to read specific attributes from the PHPDoc comment for a ReflectionProperty,
 *
 * Class ReflectionPropertyComment
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-objects/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object\Reflection
 */
class ReflectionPropertyComment extends ReflectionComment
{
  /** @var string|array */
  protected $_type;

  /**
   * Override to ensure that we have a ReflectionProperty.
   *
   * @param \ReflectionProperty $class
   */
  public function __construct(\ReflectionProperty $class)
  {
    parent::__construct($class);
  }

  /**
   * Returns the property type.
   *
   * @return string|string[]
   */
  public function getReturnType()
  {
    if (!isset($this->_type))
    {
      $this->_type = 'null';
      if ($this->has('var'))
      {
        if (preg_match('#@var\s+(\S+\s*(.*)?)$#mU', $this->raw , $m))
        {
          $this->_type = $this->resolveType($m[1]);
        }
      }
    }

    return $this->_type;
  }

  /**
   * Evaluates if the property type is a PHP built-in type.
   *
   * @return bool
   */
  public function isBuiltIn()
  {
    return $this->isBuiltInType($this->getReturnType());
  }

  /**
   * Evaluates if the property type is nullable.
   *
   * @return bool
   */
  public function isNullable()
  {
    return $this->isNullableType($this->getReturnType());
  }
}