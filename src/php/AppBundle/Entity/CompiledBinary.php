<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * Compiled Entity for AppBundle\Entity\Binary
 * 
 * To change table name or entity repository edit the AppBundle\Entity\Binary class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledBinary {
  
  /**
   * id
   * @var integer
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   * @Serializer\Expose
   * @Serializer\Type("integer")
   */
  protected $id;
  
  /**
   * gaufretteKey
   * @var string
   * @ORM\Column
   * @Serializer\Expose
   * @Serializer\Type("string")
   */
  protected $gaufretteKey;
  
  /**
   * originalName
   * @var string
   * @ORM\Column
   * @Serializer\Expose
   * @Serializer\Type("string")
   */
  protected $originalName;
  
  public function __construct() {

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
  public function getGaufretteKey() {
    return $this->gaufretteKey;
  }
  
  /**
   * @param string $gaufretteKey
   */
  public function setGaufretteKey($gaufretteKey) {
    $this->gaufretteKey = $gaufretteKey;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getOriginalName() {
    return $this->originalName;
  }
  
  /**
   * @param string $originalName
   */
  public function setOriginalName($originalName) {
    $this->originalName = $originalName;
    return $this;
  }
}
