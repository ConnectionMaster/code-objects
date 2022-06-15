<?php

namespace DevCoding\CodeObject\Object;

/**
 * Object representing a match for a regular expression.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 *
 * @package DevCoding\CodeObject\Object
 */
class RegexMatch extends \ArrayObject
{
  /** @var \Closure */
  protected $normalizer;

  public function __construct($data, $normalizer = null)
  {
    $this->normalizer = $normalizer;

    parent::__construct($data, \ArrayObject::STD_PROP_LIST + \ArrayObject::ARRAY_AS_PROPS);
  }

  public function getFullMatch()
  {
    return $this[0];
  }

  public function getFiltered($keys): array
  {
    return array_intersect_key($this->getArrayCopy(), array_fill_keys($keys, null));
  }

  public function getMatch($index_or_key)
  {
    return $this->getArrayCopy()[$index_or_key];
  }

  /**
   * @return \ArrayObject
   */
  public function getMatches(): \ArrayObject
  {
    $copy = parent::getArrayCopy();

    if (reset($copy) == $this->getFullMatch())
    {
      array_shift($copy);
    }

    return new \ArrayObject($copy, \ArrayObject::STD_PROP_LIST + \ArrayObject::ARRAY_AS_PROPS);
  }

  /**
   * @return array
   */
  public function getArrayCopy(): array
  {
    if ($normal = $this->getNormalized())
    {
      if (is_array($normal))
      {
        return $normal;
      }
    }

    return parent::getArrayCopy();
  }

  /**
   * @return array|mixed
   */
  public function getNormalized()
  {
    return $this->normalize();
  }

  /**
   * @return array|mixed
   */
  protected function normalize()
  {
    if ($this->normalizer instanceof \Closure)
    {
      return call_user_func($this->normalizer, parent::getArrayCopy());
    }

    return parent::getArrayCopy();
  }
}
