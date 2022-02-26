<?php
/**
 * BadClassException.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Exception;

/**
 * Parent class for exceptions that indicate a class that cannot be found or resolved into a fully qualified class name.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Exception
 */
class BadClassException extends \LogicException
{

}
