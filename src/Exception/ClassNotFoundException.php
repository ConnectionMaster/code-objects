<?php
/**
 * ClassNotFoundException.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Exception;

/**
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Exception
 */
class ClassNotFoundException extends BadClassException
{
  public function __construct($class = '', $code = 0, \Throwable $previous = null, $namespaces = [])
  {
    $nSpaces = is_array($namespaces) ? implode(',', $namespaces) : $namespaces;
    $suffix  = !empty($nSpaces) ? sprintf(' Checked the following namespaces: "%s"', $nSpaces) : '';
    $tmpl    = 'The class "%s" could not be found.%s';

    parent::__construct(sprintf($tmpl, $class, $suffix), $code, $previous);
  }
}
