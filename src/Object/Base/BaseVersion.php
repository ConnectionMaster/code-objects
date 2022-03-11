<?php
/**
 * BaseVersion.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevCoding\CodeObject\Object\Base;

/**
 * Object class representing a semantic software version.
 *
 * This class is based loosely on PHLAK's SemVer (https://github.com/PHLAK/SemVer), however the parser has been
 * rewritten to be more forgiving with version numbers that do not quite match the Semantic Versioning strategy,
 * include the build number when making comparisons, and provide separate classes for immutable version objects.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Object\Base
 */
abstract class BaseVersion
{
  /** @var string the raw version string given at instantiation */
  protected $raw;
  /** @var int the major version */
  protected $major;
  /** @var int the minor version */
  protected $minor;
  /** @var int|null the patch number */
  protected $patch;
  /** @var string|null the pre-release string */
  protected $pre;
  /** @var string|null the build string or number */
  protected $build;

  public function __construct($version)
  {
    $this->raw = $version;

    if ($parsed = $this->parse($version))
    {
      $this->major = (int) $parsed['major'];
      $this->minor = (int) $parsed['minor'];
      $this->patch = (int) $parsed['patch'];
      $this->pre   = $parsed['pre_release'];
      $this->build = $parsed['build'];
    }
  }

  /**
   * Creates the object from the given array, which should either contain the keys: major, minor, patch, pre_release,
   * and build, or 5 indexed keys in the order of major, minor, patch, pre_release, build.
   *
   * @return static
   */
  public static function fromArray(array $array)
  {
    $major = $array['major']       ?? $array[0]       ?? null;
    $minor = $array['minor']       ?? $array[1]       ?? null;
    $patch = $array['patch']       ?? $array[2]       ?? null;
    $pre   = $array['pre_release'] ?? $array[3] ?? null;
    $build = $array['build']       ?? $array[4]       ?? null;
    $base  = implode('.', [$major, $minor, $patch]);

    // Create the object
    $ver = new static($base);

    // Set PreRelease and Build Separately
    $ver->pre   = $pre;
    $ver->build = $build;

    return $ver;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    // Start off with major and minor.  Methods will return 0 if not set, so we'll always have these.
    $str = implode('.', [$this->getMajor(), $this->getMinor()]);
    // Add in the patch with the period prefix, if applicable.
    $str .= ($patch = $this->getPatch()) ? '.'.$patch : '';
    // Add in the prerelease with the dash prefix, if applicable.
    $str .= ($pre = $this->getPreRelease()) ? '-'.$pre : '';
    // Add in the build with the plus prefix, if applicable.
    $str .= ($build = $this->getBuild()) ? '+'.$build : '';

    return $str;
  }

  /**
   * @return int
   */
  public function getMajor()
  {
    return $this->major ?? 0;
  }

  /**
   * @return int
   */
  public function getMinor()
  {
    return $this->minor ?? 0;
  }

  /**
   * @return int|null
   */
  public function getPatch()
  {
    return $this->patch;
  }

  /**
   * @return string|null
   */
  public function getPreRelease()
  {
    return $this->pre;
  }

  /**
   * @return string|null
   */
  public function getBuild()
  {
    return $this->build;
  }

  /**
   * @return string
   */
  public function getRaw()
  {
    return $this->raw;
  }

  /**
   * @return bool
   */
  public function isPreRelease()
  {
    return isset($this->pre);
  }

  /**
   * @param bool $assoc
   *
   * @return array
   */
  public function toArray($assoc = true)
  {
    $arr = [
        'major'       => $this->getMajor(),
        'minor'       => $this->getMinor(),
        'patch'       => $this->getPatch(),
        'pre_release' => $this->getPreRelease(),
        'build'       => $this->getBuild(),
    ];

    return $assoc ? $arr : array_values($arr);
  }

  /**
   * Check if this Version object is greater than another.
   *
   * @param BaseVersion $version The version to compare to
   *
   * @return bool True if this Version object is greater than the comparing object, otherwise false
   */
  public function gt(BaseVersion $version): bool
  {
    return $this->compare($this, $version) > 0;
  }

  /**
   * Check if this Version object is less than another.
   *
   * @param BaseVersion $version The version to compare to
   *
   * @return bool True if this Version object is less than the comparing object, otherwise false
   */
  public function lt(BaseVersion $version): bool
  {
    return $this->compare($this, $version) < 0;
  }

  /**
   * Check if this Version object is equal to than another.
   *
   * @param BaseVersion $version The version to compare to
   *
   * @return bool True if this Version object is equal to the comparing object, otherwise false
   */
  public function eq(BaseVersion $version): bool
  {
    return 0 === $this->compare($this, $version);
  }

  /**
   * Check if this Version object is not equal to another.
   *
   * @param BaseVersion $version The version to compare to
   *
   * @return bool True if this Version object is not equal to the comparing object, otherwise false
   */
  public function neq(BaseVersion $version): bool
  {
    return 0 !== $this->compare($this, $version);
  }

  /**
   * Check if this Version object is greater than or equal to another.
   *
   * @param BaseVersion $version The version to compare to
   *
   * @return bool True if this Version object is greater than or equal to the comparing object, otherwise false
   */
  public function gte(BaseVersion $version): bool
  {
    return $this->compare($this, $version) >= 0;
  }

  /**
   * Check if this Version object is less than or equal to another.
   *
   * @param BaseVersion $version The version to compare to
   *
   * @return bool True if this Version object is less than or equal to the comparing object, otherwise false
   */
  public function lte(BaseVersion $version): bool
  {
    return $this->compare($this, $version) <= 0;
  }

  /**
   * Compares the two given versions, returning an integer similar to the PHP spaceship operator.
   * Based on the Comparable trait in PHLAK/SemVer, but expanded to compare the raw input string
   * and the build number, if needed.
   *
   * @return int
   */
  protected function compare(BaseVersion $v1, BaseVersion $v2)
  {
    if ($v1->getRaw() === $v1->getRaw())
    {
      // For identical raw strings, consider them equal
      return 0;
    }

    $vBase1 = array_slice($v1->toArray(false), 0, 3);
    $vBase2 = array_slice($v2->toArray(false), 0, 3);
    $vBaseC = $vBase1 <=> $vBase2;

    if (0 !== $vBaseC)
    {
      return $vBaseC;
    }

    // Both version numbers are the same.  Compare based on PreRelease...
    if ($v1->isPreRelease() && !$v2->isPreRelease())
    {
      return -1;
    }
    elseif (!$v1->isPreRelease() && $v2->isPreRelease())
    {
      return 1;
    }
    elseif ($v1->isPreRelease() && $v2->isPreRelease())
    {
      $vPreC = $this->comparePart($v1->getPreRelease(), $v2->getPreRelease());

      if (0 !== $vPreC)
      {
        return $vPreC;
      }
    }

    // Both PreReleases are the same. Compare based on Build Number...
    $v1Build = $v1->getBuild();
    $v2Build = $v2->getBuild();

    if ($v1Build && !$v2Build)
    {
      return -1;
    }
    elseif (!$v1Build && $v2Build)
    {
      return 1;
    }
    elseif ($v1Build && $v2Build)
    {
      $vBuildC = $this->comparePart($v1Build, $v2Build);

      if (0 !== $vBuildC)
      {
        return $vBuildC;
      }
    }

    // Everything looks the same...
    return 0;
  }

  /**
   * Compares the version parts given, typically the pre-release or build numbers, returning an integer similar to the
   * functionality of the PHP Spaceship operator.
   *
   * @param string $p1 The first version part string to compare
   * @param string $p2 the second version part string to compare
   *
   * @return int
   */
  protected function comparePart($p1, $p2)
  {
    // Normalize
    $vNorm1 = str_replace([' ', '_', '-'], '.', $p1 ?? '');
    $vNorm2 = str_replace([' ', '_', '-'], '.', $p2 ?? '');

    // Break Into Parts
    $vParts1 = explode('.', $vNorm1);
    $vParts2 = explode('.', $vNorm2);

    // Pad the Array
    $vPad1 = array_pad($vParts1, count($vParts2), null);
    $vPad2 = array_pad($vParts2, count($vParts1), null);

    // Return the comparison
    return $vPad1 <=> $vPad2;
  }

  /**
   * Parse the version number into an array with the keys:  major, minor, patch, pre_release, build.  Strings that are
   * missing one or more of these parts will return either 0 or null for the key, depending on whether the key is
   * essential to identifying the version.
   *
   * @param string $v
   *
   * @return array|null
   */
  protected function parse($v)
  {
    if ($this->isStringable($v))
    {
      // Strip extraneous spaces
      $v = trim($v);

      // Strip preceeding 'version'
      if (0 === stripos($v, 'version'))
      {
        $v = substr($v, 8);
      }

      $empty = array_fill_keys(['major', 'minor', 'patch', 'pre_release', 'build'], null);
      if (preg_match('#^[vV]?(?<major>\d+)(?:\.(?<minor>\d+)(?:\.(?<patch>\d+))?)?(?<remainder>.*)$#', $v, $m))
      {
        $parsed = array_intersect_key($m, $empty) + $empty;

        if (!empty($m['remainder']))
        {
          if (preg_match('#(?:[.\s-](?<pre_release>[^\s+]+))?(?:[+\s](?<build>.+))?$#', $m['remainder'], $r))
          {
            $parsed = array_intersect_key($r, $empty) + $parsed;
          }
        }

        return array_merge(['major' => '0', 'minor' => '0'], $parsed);
      }
      elseif (preg_match('#Build (?<build>.*)$#', $v, $m))
      {
        $jbuild = array_merge(['major' => '0', 'minor' => '0'], $m);

        return array_intersect_key($jbuild, $empty) + $empty;
      }
    }

    return null;
  }

  /**
   * Evaluates whether the given value can be cast to a string without error.
   *
   * @param mixed $val the value to evaluate
   *
   * @return bool TRUE if the value may be safely cast to a string
   */
  public function isStringable($val)
  {
    return is_scalar($val) || is_object($val) && method_exists($val, '__toString');
  }
}
