<?php

namespace Webforge\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Compiled Entity for Webforge\CmsBundle\Entity\User
 *
 * To change table name or entity repository edit the Webforge\CmsBundle\Entity\User class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledUser extends \FOS\UserBundle\Model\User
{
  
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
     * firstName
     * @var string
     * @ORM\Column
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    protected $firstName;
  
    /**
     * lastName
     * @var string
     * @ORM\Column
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    protected $lastName;
  
    public function __construct()
    {
        parent::__construct();
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
    public function getFirstName()
    {
        return $this->firstName;
    }
  
    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }
  
    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
  
    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }
}
