<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\User
 *
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\User class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledUser
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
     * email
     * @var string
     * @ORM\Column(length=210)
     */
    protected $email;
  
    /**
     * special
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $special;
  
    public function __construct($email)
    {
        $this->email = $email;
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
    public function getEmail()
    {
        return $this->email;
    }
  
    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
  
    /**
     * @return string
     */
    public function getSpecial()
    {
        return $this->special;
    }
  
    /**
     * @param string $special
     */
    public function setSpecial($special = null)
    {
        $this->special = $special;
        return $this;
    }
}
