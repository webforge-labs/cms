<?php

namespace AppBundle\Entity;

use Webforge\Common\DateTime\DateTime;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * Compiled Entity for AppBundle\Entity\MediaTree
 * 
 * To change table name or entity repository edit the AppBundle\Entity\MediaTree class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledMediaTree {
  
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
   * content
   * @var string
   * @ORM\Column(type="text")
   * @Serializer\Expose
   * @Serializer\Type("string")
   */
  protected $content;
  
  /**
   * created
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime")
   * @Serializer\Expose
   * @Serializer\Type("WebforgeDateTime")
   */
  protected $created;
  
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
  public function getContent() {
    return $this->content;
  }
  
  /**
   * @param string $content
   */
  public function setContent($content) {
    $this->content = $content;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getCreated() {
    return $this->created;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $created
   */
  public function setCreated(DateTime $created) {
    $this->created = $created;
    return $this;
  }
}
