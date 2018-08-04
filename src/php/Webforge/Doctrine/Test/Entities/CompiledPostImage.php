<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\PostImage
 *
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\PostImage class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledPostImage
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
     * position
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $position;
  
    /**
     * post
     * @var Webforge\Doctrine\Test\Entities\Post
     * @ORM\ManyToOne(targetEntity="Webforge\Doctrine\Test\Entities\Post", inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $post;
  
    /**
     * binary
     * @var Webforge\Doctrine\Test\Entities\Binary
     * @ORM\ManyToOne(targetEntity="Webforge\Doctrine\Test\Entities\Binary")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $binary;
  
    public function __construct(Binary $binary, Post $post, $position)
    {
        if (isset($binary)) {
            $this->setBinary($binary);
        }
        if (isset($post)) {
            $this->setPost($post);
        }
        $this->position = $position;
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
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }
  
    /**
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }
  
    /**
     * @return Webforge\Doctrine\Test\Entities\Post
     */
    public function getPost()
    {
        return $this->post;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $post
     */
    public function setPost(Post $post)
    {
        if (isset($this->post) && $this->post !== $post) {
            $this->post->removeImage($this);
        }
        $this->post = $post;
        $post->addImage($this);
        return $this;
    }
  
    /**
     * @return Webforge\Doctrine\Test\Entities\Binary
     */
    public function getBinary()
    {
        return $this->binary;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Binary $binary
     */
    public function setBinary(Binary $binary)
    {
        $this->binary = $binary;
        return $this;
    }
}
