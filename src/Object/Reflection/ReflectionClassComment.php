<?php

namespace DevCoding\CodeObject\Object\Reflection;

/**
 * Reflection-style class designed to read specific attributes from the PHPDoc comment for a ReflectionClass,
 *
 * Class ReflectionClassComment
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-objects/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object\Reflection
 */
class ReflectionClassComment extends ReflectionComment
{
  /** @var array */
  protected $_properties;
  /** @var array */
  protected $_methods;

  /**
   * Override to ensure that we have a reflection class.
   *
   * @param \ReflectionClass $class
   */
  public function __construct(\ReflectionClass $class)
  {
    parent::__construct($class);
  }

  /**
   * Returns an array of attributes, including type & comment for a method specified in the PHPdoc comment for the class.
   *
   * @param string $name
   *
   * @return array
   */
  public function getMethod(string $name): array
  {
    return $this->getMethods()[$name];
  }

  /**
   * Returns an array of methods attributes specified in the PHPdoc comment for the class.
   *
   * @return array[]
   */
  public function getMethods(): array
  {
    if (!isset($this->_methods))
    {
      $this->_methods = array();
      if (preg_match_all('#@method\s+(\S+)\s+(\S+)\(([^)]+)?\)\s*([^*/]+)?#m', $this->raw, $m, PREG_SET_ORDER))
      {
        foreach($m as $match)
        {
          if ($name = $match[2] ?? null)
          {
            $type = $match[1];

            $this->_methods[$name] = array('type' => $this->resolveType($type), 'comment' => trim($match[3] ?? ''));
          }
        }
      }
    }

    return $this->_methods;
  }

  /**
   * Returns an array of attributes, including type & comment for a property specified in the PHPdoc comment
   * for the class.
   *
   * @param string $name
   *
   * @return array
   */
  public function getProperty(string $name): array
  {
    return $this->getProperties()[$name];
  }

  /**
   * Returns an array of property attributes for each property specified in the PHPdoc comments for the class.
   *
   * @return array[]
   */
  public function getProperties(): array
  {
    if (!isset($this->_properties))
    {
      $this->_properties = array();
      if (preg_match_all('#@property\s+(\S+)\s+\$(\S+)\s*([^*/]+)?#m', $this->raw, $m, PREG_SET_ORDER))
      {
        foreach($m as $match)
        {
          if ($name = $match[2] ?? null)
          {
            $type = $match[1];

            $this->_properties[$name] = array('type' => $this->resolveType($type), 'comment' => trim($match[3] ?? ''));
          }
        }
      }
    }

    return $this->_properties;
  }

  /**
   * Evaluates if the PHPDoc comments for the class have a method comment for the given method.
   *
   * @param string $name
   *
   * @return bool
   */
  public function hasMethod(string $name): bool
  {
    return array_key_exists($name, $this->getMethods());
  }

  /**
   * Evaluates if the PHPDoc comments for the class have a property comment for the given property.
   *
   * @param string $name
   *
   * @return bool
   */
  public function hasProperty($name): bool
  {
    return array_key_exists($name, $this->getProperties());
  }
}