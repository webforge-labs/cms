<?php

namespace Webforge\CmsBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * Compiled Entity for Webforge\CmsBundle\Entity\User
 * 
 * To change table name or entity repository edit the Webforge\CmsBundle\Entity\User class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledUser extends \FOS\UserBundle\Model\User {
  
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
   * displayName
   * @var string
   * @ORM\Column
   * @Serializer\Expose
   * @Serializer\Type("string")
   */
  protected $displayName;
  
  public function __construct() {
    parent::__construct();
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
  public function getDisplayName() {
    return $this->displayName;
  }
  
  /**
   * @param string $displayName
   */
  public function setDisplayName($displayName) {
    $this->displayName = $displayName;
    return $this;
  }
}
