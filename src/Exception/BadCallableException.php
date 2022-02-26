<?php
/**
 * BadCallableException.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Exception;

/**
 * Exception thrown when a string or array cannot be properly resolved into a callable.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Exception
 */
class BadCallableException extends \BadMethodCallException
{

}
