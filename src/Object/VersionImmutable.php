<?php
/**
 * VersionImmutable.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Object;

use DevCoding\CodeObject\Object\Base\BaseVersion;

/**
 * Immutable object class representing a semantic software version.
 *
 * This class is based loosely on PHLAK's SemVer (https://github.com/PHLAK/SemVer), however the parser has been
 * rewritten to be more forgiving with version numbers that do not quite match the Semantic Versioning strategy,
 * include the build number when making comparisons, and provide separate classes for immutable version objects.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object
 */
class VersionImmutable extends BaseVersion
{
  // Note that all code for this class is in the abstract BaseVersion parent. This space intentionally left blank.
}
