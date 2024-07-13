<?php

namespace DevCoding\CodeObject\Object\Reflection;

/**
 * Reflection-style class designed to read specific attributes from the PHPDoc comment for a ReflectionMethod,
 *
 * Class ReflectionMethodComment
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-objects/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object\Reflection
 */
class ReflectionMethodComment extends ReflectionComment
{
  /**
   * Override to guarantee we are using a ReflectionMethod.
   *
   * @param \ReflectionMethod $class
   */
  public function __construct(\ReflectionMethod $class)
  {
    parent::__construct($class);
  }

  /**
   * Returns an array of parameter attributes, including comment and type of each.
   *
   * @return array
   */
  public function getParams(): array
  {
    if (!isset($this->_params))
    {
      $this->_params = array();
      if (preg_match_all('#@param\s+(\S+)\s+\$(\S+)\s*([^*/]+)?#m', $this->raw, $m, PREG_SET_ORDER))
      {
        foreach($m as $match)
        {
          if ($name = $match[2] ?? null)
          {
            $type = $match[1];

            $this->_params[$name] = array('type' => $this->resolveType($type), 'comment' => $match[3] ?? null);
          }
        }
      }
    }

    return $this->_params;
  }

  /**
   * Parses and returns the return type.
   *
   * @return string[]|string|null
   */
  public function getReturnType()
  {
    if (!isset($this->_return))
    {
      $this->_return = 'void';
      if ($this->has('return'))
      {
        if (preg_match('#@return\s?(\S+\s*([^*/]+))$#m', $this->raw , $m))
        {
          $this->_return = $this->resolveType($m[1]);
        }
      }
    }

    return $this->_return;
  }

  /**
   * Parses and returns the type of the given named parameter.
   *
   * @param string $name
   *
   * @return string|null
   */
  public function getParamType(string $name)
  {
    $params = $this->getParams();
    if (isset($params[$name]['type']))
    {
      return $params[$name]['type'];
    }

    return null;
  }

  /**
   * Evaluates if the return type is a built-in PHP type.
   *
   * @return bool
   */
  public function isBuiltIn()
  {
    return $this->isBuiltInType($this->getReturnType());
  }

  /**
   * Evaluates if the return type is nullable.
   *
   * @return bool
   */
  public function isNullable()
  {
    return $this->isNullableType($this->getReturnType());
  }
}