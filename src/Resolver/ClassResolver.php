<?php
/**
 * ClassResolver.php
 *
 * (c) AMJones <am@jonesiscoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DevCoding\CodeObject\Resolver;

use DevCoding\CodeObject\Exception\AmbiguousClassException;
use DevCoding\CodeObject\Exception\ClassNotFoundException;
use DevCoding\CodeObject\Object\ClassString;

/**
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/code-object/blob/main/LICENSE
 * @package DevCoding\CodeObject\Resolver
 */
class ClassResolver
{
  /** @var array */
  protected $namespaces;
  /** @var string|null */
  protected $projectDir;

  /**
   * @param string[]|object[] $namespaces_or_siblings Namespaces or siblings to resolve non-fully qualified classes
   * @param string            $projectDir             The root directory of the project or application
   */
  public function __construct($namespaces_or_siblings, $projectDir = null)
  {
    $this->projectDir = $projectDir;

    foreach ($namespaces_or_siblings as $item)
    {
      if (is_object($item))
      {
        $item = get_class($item);
      }

      if (class_exists($item))
      {
        // Since we know this is a class, we'll chop off the class name.
        if ($ns = (new ClassString($item))->getNamespace())
        {
          $this->namespaces[] = $ns;
        }
      }
      elseif (false !== strpos($item, '\\'))
      {
        // As there is no way to verify that a namespace exists, this is the best we can do.
        $this->namespaces[] = $item;
      }
    }
  }

  // region //////////////////////////////////////////////// Public Methods

  /**
   * Resolves the given string into a fully qualified class name.
   *
   * @return string the string to resolve
   * @throws AmbiguousClassException|ClassNotFoundException if the class cannot be resolved
   */
  public function resolve($name)
  {
    if (class_exists($name))
    {
      return $name;
    }
    else
    {
      $classes = [];
      foreach ($this->getNamespaces() as $namespace)
      {
        $fqcn = $namespace.'\\'.$name;
        if (class_exists($fqcn))
        {
          $classes[] = $fqcn;
        }
      }

      if (count($classes) > 1)
      {
        // Too Big
        throw new AmbiguousClassException($name, $classes);
      }
      elseif (empty($classes))
      {
        // Too Small
        throw new ClassNotFoundException($name, $this->namespaces);
      }
      else
      {
        // Just Right
        return array_pop($classes);
      }
    }
  }

  /**
   * Returns all classes hinted by the namespaces given at the instantiation of in this ClassResolver.
   *
   * @return array
   */
  public function all()
  {
    $aClasses = [];
    foreach ($this->namespaces as $namespace)
    {
      $nClasses = $this->getClassesInNamespace($namespace);
      $aClasses = array_merge($aClasses, $nClasses);
    }

    return array_unique($aClasses);
  }

  // endregion ///////////////////////////////////////////// Public Methods

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * Returns an array of classes by fully qualified class name in the given namespace, as derived from the PHP files
   * present in the resolved directory for the namespace.
   *
   * @param string $namespace the namespace to find classes for
   *
   * @return string[] the array of classes that was found within the namespace
   */
  protected function getClassesInNamespace($namespace)
  {
    $classes = [];
    if ($dir = $this->getNamespaceDirectory($namespace))
    {
      foreach (glob($dir.'/*.php') as $file)
      {
        $fqcn = $namespace.'\\'.str_replace('.php', '', $file);

        if (class_exists($fqcn))
        {
          $classes[] = $fqcn;
        }
      }
    }

    return $classes;
  }

  /**
   * Returns an array of namespaces defined in the project composer.json
   *
   * @return string[] the array of namespaces
   */
  protected function getDefinedNamespaces()
  {
    $dir = $this->getProjectDir();
    if (is_dir($dir))
    {
      $composerJsonPath = $this->getProjectDir().'/composer.json';
      $composerConfig   = json_decode(file_get_contents($composerJsonPath));

      return (array) $composerConfig->autoload->{'psr-4'};
    }

    return [];
  }

  /**
   * Returns the absolute path to the given name space in a PSR-4 directory structure
   *
   * @param string $namespace the namespace for which to get the directory
   *
   * @return string|null the directory, or null if no such directory exists
   */
  protected function getNamespaceDirectory($namespace)
  {
    $defined = $this->getDefinedNamespaces();

    foreach ($defined as $ns => $dir)
    {
      if ($ns == $namespace)
      {
        return $this->getProjectDir().$dir;
      }
    }

    return null;
  }

  /**
   * Returns the namespaces given at instantiation for class resolution.
   *
   * @return string[] the array of namespaces
   */
  protected function getNamespaces()
  {
    if (!isset($this->namespaces))
    {
      $this->namespaces = $this->getDefinedNamespaces();
    }

    return $this->namespaces;
  }

  /**
   * Returns the absolute path to the project directory, either from the value given at instantiation or derived from
   * the location of the application or project's composer.json
   *
   * @return string
   */
  protected function getProjectDir()
  {
    if (null === $this->projectDir)
    {
      $this->projectDir = (new ProjectResolver())->getDir();
    }

    return $this->projectDir;
  }

  // endregion ///////////////////////////////////////////// Helper Methods
}
