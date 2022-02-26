<?php
/**
 * AmbiguousClassException.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Exception;

/**
 * Exception thrown in situations where multiple classes match criteria, and a single fully qualified class name cannot
 * be chosen.
 *
 * @author  AMJones <am@jonesiscoding.com
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Exception
 */
class AmbiguousClassException extends BadClassException
{
  public function __construct($class = '', $classes = [], $code = 0, \Throwable $previous = null)
  {
    $classes = is_array($classes) ? implode(',', $classes) : $classes;
    $suffix  = !empty($classes) ? sprintf(' Did you mean: "%s"', $classes) : '';
    $tmpl    = 'The class "%s" could not be resolved to a single class.%s';

    parent::__construct(sprintf($tmpl, $class, $suffix), $code, $previous);
  }
}
