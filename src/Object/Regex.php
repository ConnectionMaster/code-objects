<?php

namespace DevCoding\CodeObject\Object;

/**
 * Object representing a Regular Expression Pattern
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 *
 * @package DevCoding\CodeObject\Object
 */
class Regex
{
  protected $pattern;
  /** @var \Closure|null */
  protected $normalizer;

  /**
   * @param string        $pattern
   * @param \Closure|null $normalizer
   */
  public function __construct(string $pattern, $normalizer = null)
  {
    $this->pattern    = $pattern;
    $this->normalizer = $normalizer;
  }

  /**
   * @param string   $subject
   * @param int|null $offset
   *
   * @return RegexMatch|false
   */
  public function match(string $subject, $offset = null)
  {
    if (preg_match($this->pattern, $subject, $m, null, $offset))
    {
      return new RegexMatch($m);
    }

    return false;
  }

  /**
   * @param string   $subject
   * @param int|null $offset
   *
   * @return RegexMatch[]|false
   */
  public function matchAll(string $subject, $offset = null)
  {
    if (preg_match_all($this->pattern, $subject, $m, PREG_SET_ORDER, $offset))
    {
      $output = [];
      foreach ($m as $match)
      {
        $output[] = new RegexMatch($match, $this->normalizer);
      }

      return $output;
    }

    return false;
  }
}
