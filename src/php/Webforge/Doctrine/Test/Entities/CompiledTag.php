<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Tag
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Tag class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledTag {
  
  /**
   * id
   * @var integer
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id;
  
  /**
   * label
   * @var string
   * @ORM\Column
   */
  protected $label;
  
  public function __construct($label) {
    $this->label = $label;
  }
  
  /**
   * @return integer
   */
  public function getId() {
    return $this->id;
  }
  
  /**
   * @param integer $id
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  /**
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
}
