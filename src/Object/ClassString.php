<?php

namespace DevCoding\CodeObject\Object;

class ClassString extends ConstructString
{
  /** @var string */
  protected $namespace;
  /** @var string */
  protected $short;

  /**
   * @return bool
   */
  public function exists()
  {
    return class_exists($this->string);
  }

  /**
   * @return string
   */
  public function getName()
  {
    if (!isset($this->short))
    {
      try
      {
        $this->short = (new \ReflectionObject($this->string))->getShortName();
      }
      catch (\Exception $e)
      {
        $this->short = substr(strrchr($this->string, '\\'), 1);
      }
    }

    return $this->short;
  }

  /**
   * @return string
   */
  public function getNamespace()
  {
    if (!isset($this->namespace))
    {
      try
      {
        $this->namespace = (new \ReflectionObject($this->string))->getNamespaceName();
      }
      catch (\Exception $e)
      {
        $this->namespace = str_replace($this->getName().'\\', '', $this->string);
      }
    }

    return $this->namespace;
  }
}
