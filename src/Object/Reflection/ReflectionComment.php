<?php

namespace DevCoding\CodeObject\Object\Reflection;

/**
 * Reflection-style class designed to read specific attributes from the PHPDoc comment for a ReflectionProperty,
 * ReflectionClass, or ReflectionMethod.
 *
 * Class ReflectionComment
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-objects/blob/main/LICENSE
 * @package AppBundle\Object\Code
 */
class ReflectionComment
{
  const BUILT_IN = array('array', 'callable', 'bool', 'float', 'int', 'string', 'iterable', 'object', 'mixed');

  /** @var \ReflectionClass */
  protected $class;
  /** @var \ReflectionParameter|\ReflectionMethod */
  protected $object;
  /** @var string */
  protected $raw;
  /** @var array */
  protected $_params;
  /** @var string[]|string */
  protected $_return;
  /** @var string[] */
  protected $_imports;

  /**
   * @param \ReflectionMethod|\ReflectionProperty|\ReflectionClass $object
   */
  public function __construct($object)
  {
    $this->object = $object;
    $this->raw   = $object->getDocComment();
  }

  /**
   * @param string|array $type
   *
   * @return bool
   */
  protected function isBuiltInType($type): bool
  {
    if (!is_array($type))
    {
      if ('void' === $type)
      {
        return false;
      }

      return in_array($type, self::BUILT_IN);
    }
    else
    {
      $hasBuiltIn = false;
      $hasOther = false;
      foreach($type as $ret)
      {
        if (in_array($ret, self::BUILT_IN))
        {
          $hasBuiltIn = true;
        }
        else
        {
          $hasOther = true;
        }
      }

      return $hasBuiltIn && !$hasOther;
    }
  }

  /**
   * @return bool
   */
  public function isDeprecated(): bool
  {
    return preg_match('#@deprecated#', $this->raw);
  }

  /**
   * @return bool
   */
  public function isInternal(): bool
  {
    return preg_match('#@internal#', $this->raw);
  }

  /**
   * @param string|array $type
   *
   * @return bool
   */
  public function isNullableType($type): bool
  {
    if ('null' === $type)
    {
      return true;
    }
    elseif (is_array($type))
    {
      if (in_array('null', $type, true))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @param string $field
   *
   * @return bool
   */
  public function has($field)
  {
    return false !== strpos($this->raw, '@' . $field);
  }

  /**
   * @param string $field
   *
   * @return string|true|null
   */
  public function get($field)
  {
    if ($this->has($field))
    {
      if (preg_match('#@'.$field.'\s?([^\s]+\s*([^*/]+))?$#m', $this->raw, $matches))
      {
        $value = $matches[1] ?? null;
        $value = $value ? trim($value) : null;

        return !empty($value) ? $value : true;
      }
    }

    return null;
  }

  /**
   * @return \ReflectionClass
   */
  public function getDeclaringClass(): \ReflectionClass
  {
    return $this->object instanceof \ReflectionClass ? $this->object : $this->object->getDeclaringClass();
  }

  /**
   * @param $type
   *
   * @return string[]|string|null
   */
  protected function resolveType($type)
  {
    if (false !== strpos($type, '|'))
    {
      $types = array();
      $all   = explode('|', $type);

      foreach($all as $one)
      {
        $types[] = $this->resolveType($one);
      }

      return $types;
    }

    if (in_array($type, self::BUILT_IN))
    {
      return $type;
    }
    elseif (class_exists($type, true))
    {
      return $type;
    }
    else
    {
      $namespace = $this->getDeclaringClass()->getNamespaceName();
      if (class_exists($namespace . "\\" . $type))
      {
        return $namespace . "\\" . $type;
      }

      $imports = $this->getImports();

      return $imports[$type] ?? null;
    }
  }

  /**
   * Returns the _imports property. Upon first use, populates the property by parsing the code of this object's
   * ReflectionClass for 'use' imports above the 'abstract class' declaration, as well as adding any imports from
   * included traits.
   *
   * @return array Short => Fully Qualified Class Name
   */
  private function getImports(): array
  {
    if (!isset($this->_imports))
    {
      // Make sure we initialize the array, so we don't do this repeatedly if there are no imports.
      $this->_imports = array();

      // Get the file contents
      $class = $this->getDeclaringClass();

      // Add the Imports from the Class
      $this->addImportsFromFile($class->getFileName());

      // Add Imports from Any Traits
      $traits = $class->getTraits();
      if (!empty($traits))
      {
        foreach($traits as $trait)
        {
          $this->addImportsFromFile($trait->getFileName());
        }
      }
    }

    return $this->_imports;
  }

  /**
   * Adds the imports from the given PHP file to the $_imports property.
   *
   * @param string $file
   */
  private function addImportsFromFile(string $file)
  {
    $contents = explode("\n", file_get_contents($file));
    foreach($contents as $line)
    {
      $line = trim($line);
      if (preg_match('/^(class|interface|abstract|trait|static)\s/', $line))
      {
        // If we've gotten to this line then we're past the imports
        return;
      }
      else
      {
        if (preg_match('/use\s+(\S+)(\s+as\s+(.*))?;/', $line, $matches))
        {
          if (!$key = $matches[3] ?? null)
          {
            $key = substr(strrchr($matches[1], '\\'), 1);
          }

          $this->_imports[$key] = $matches[1];
        }
      }
    }
  }
}