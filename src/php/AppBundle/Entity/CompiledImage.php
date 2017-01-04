<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * Compiled Entity for AppBundle\Entity\Image
 * 
 * To change table name or entity repository edit the AppBundle\Entity\Image class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledImage {
  
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
   * binary
   * @var AppBundle\Entity\Binary
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Binary")
   * @ORM\JoinColumn(nullable=false)
   * @Serializer\Expose
   * @Serializer\Type("AppBundle\Entity\Binary")
   */
  protected $binary;
  
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
   * @return AppBundle\Entity\Binary
   */
  public function getBinary() {
    return $this->binary;
  }
  
  /**
   * @param AppBundle\Entity\Binary $binary
   */
  public function setBinary(Binary $binary) {
    $this->binary = $binary;
    return $this;
  }
}
