<?php
/**
 * ConstructString.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Object;

/**
 * Object for a string that represents a coding construct of some kind, such as a function, class, method, callable
 * key, variable name, etc.  Offers methods for manipulating that string.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 *
 * @package DevCoding\CodeObject\Object
 */
class ConstructString
{
  use TransliterateTrait;

  const PATTERN_SLUGIFY = '#[^0-9a-z]+#i';

  /** @var string */
  protected $string;

  /**
   * Transliterates and unquotes the string while instantiating the object.
   *
   * @param string $string
   */
  public function __construct($string)
  {
    $this->string = $this->transliterateString($this->unquoteString($string));
  }

  // region //////////////////////////////////////////////// Public Methods

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->string;
  }

  /**
   * Converts the string to camelCase.  (IE - Converts 'class_name' to 'className')
   *
   * @return string The camelCase string
   */
  public function camelize()
  {
    return lcfirst( $this->classify() );
  }

  /**
   * Converts this string into PascalCase. IE- Converts 'class_name' to 'ClassName'.
   *
   * @author Jonathan H. Wage <jonwage@gmail.com> (Borrowed from Doctrine Inflector classify)
   * @return string The PascalCase string
   */
  public function classify()
  {
    return str_replace([' ', '_', '-'], '', ucwords($this->string, ' _-'));
  }

  /**
   * Converts this string into a CSS safe string that may be used for CSS class names or HTML5 IDs.
   *
   * @param string $sep             The separator to use.  Defaults to -
   * @param bool   $spaces          Allow spaces in the result.  Defaults to FALSE
   * @param bool   $force_lowercase Force the result to lowercase.  Defaults to FALSE
   *
   * @return string|string[]|null
   *
   * @throws \Exception
   */
  public function css($sep = '-', $spaces = false, $force_lowercase = false)
  {
    // Replace Spaces
    $add    = ( $spaces ) ? ['\s', preg_quote( $sep, '/' )] : [null, preg_quote( $sep, '/' )];
    $regex  = vsprintf( '/([^a-zA-Z0-9_%s%s]+)/', $add );
    $string = preg_replace( $regex, $sep, $this->string );

    // Force Lowercase
    if ( $force_lowercase )
    {
      $string = strtolower( $string );
    }

    // Make sure first character is NOT a number.
    if ( is_numeric( substr( $string, 0, 1 ) ) )
    {
      $digits = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
      $string = $digits[substr( $string, 0, 1 )].substr( $string, 1 );
    }

    return $string;
  }

  /**
   * Evaluates if this string is an existing fully qualified class name.
   *
   * @return bool
   */
  public function isClass()
  {
    return class_exists($this->string);
  }

  /**
   * Evaluates if this string is an existing fully qualified interface name.
   *
   * @return bool
   */
  public function isInterface()
  {
    return interface_exists($this->string);
  }

  /**
   * Creates a "slug" from this string replacing all the spacing characters with a separator, converting the string to
   * lower case, and removing any non-alphanumeric characters.
   *
   * @param string $sep     the separator to use, a dash by default
   * @param string $pattern Regex pattern to use for replacement
   *
   * @return string The string as a slug
   */
  public function slugify($sep = '-', $pattern = self::PATTERN_SLUGIFY)
  {
    return strtolower(trim( preg_replace( $pattern, $sep, $this->string ), ' -' ));
  }

  /**
   * Converts this string into snake_case. IE- Converts 'ClassName' to 'class_name'.
   *
   * @author Jonathan H. Wage <jonwage@gmail.com> (Borrowed from Doctrine Inflector tableize)
   * @return string
   */
  public function snakeize()
  {
    return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $this->string));
  }

  // endregion ///////////////////////////////////////////// Public Methods
}
