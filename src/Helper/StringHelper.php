<?php

namespace DevCoding\CodeObject\Helper;

class StringHelper
{
  /** @var StringHelper */
  protected static $instance;

  public static function create(): StringHelper
  {
    if (!static::$instance instanceof StringHelper)
    {
      static::$instance = new StringHelper();
    }

    return static::$instance;
  }

  /**
   * Evaluates whether the given value can be cast to a string without error.
   *
   * @param mixed $val the value to evaluate
   *
   * @return bool TRUE if the value may be safely cast to a string
   */
  public function isStringable($val): bool
  {
    return is_scalar($val) || is_object($val) && method_exists($val, '__toString');
  }
}