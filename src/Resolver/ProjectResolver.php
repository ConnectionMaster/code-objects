<?php
/**
 * ProjectResolver.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Resolver;

/**
 * Contains methods to determine the root directory of the current application or project by locating the composer.json,
 * and ignoring any composer.json that resides within the 'vendor' directory.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Resolver
 */
class ProjectResolver
{
  /** @var string */
  protected $projectDir;

  /**
   * @param string $projectDir Optional project directory, if already known
   */
  public function __construct($projectDir = null)
  {
    $this->projectDir = $projectDir;
  }

  // region //////////////////////////////////////////////// Public Methods

  /**
   * Determines the location of the project root directory by locating the composer.json.  Any composer.json files in
   * the 'vendor' directory are ignored, allowing this method to be used within a composer installed library.
   *
   * @return string
   */
  public function getDir()
  {
    if (null === $this->projectDir)
    {
      $r = new \ReflectionObject($this);

      if (!file_exists($dir = $r->getFileName()))
      {
        throw new \LogicException('Cannot auto-detect project directory.');
      }

      $dir = $rootDir = \dirname($dir);
      while (false !== strpos($dir, 'vendor') || !file_exists($dir.'/composer.json'))
      {
        if ($dir === \dirname($dir))
        {
          return $this->projectDir = $rootDir;
        }
        $dir = \dirname($dir);
      }
      $this->projectDir = $dir;
    }

    return $this->projectDir;
  }

  // endregion ///////////////////////////////////////////// Public Methods
}
