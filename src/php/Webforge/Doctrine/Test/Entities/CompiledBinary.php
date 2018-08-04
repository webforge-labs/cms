<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Binary
 *
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Binary class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledBinary
{
  
  /**
   * id
   * @var integer
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
    protected $id;
  
    /**
     * path
     * @var string
     * @ORM\Column
     */
    protected $path;
  
    public function __construct($path)
    {
        $this->path = $path;
    }
  
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
  
    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
  
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
  
    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
}
