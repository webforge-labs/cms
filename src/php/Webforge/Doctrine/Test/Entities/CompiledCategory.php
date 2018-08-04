<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\Common\Collections\Collection;
use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Category
 *
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Category class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledCategory
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
     * posts
     * @var Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
     * @ORM\ManyToMany(targetEntity="Webforge\Doctrine\Test\Entities\Post", mappedBy="categories")
     * @ORM\JoinTable(name="posts2categories", joinColumns={@ORM\JoinColumn(name="post_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="category_id", onDelete="cascade")})
     */
    protected $posts;
  
    public function __construct()
    {
        $this->posts = new ArrayCollection();
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
     * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
     */
    public function getPosts()
    {
        return $this->posts;
    }
  
    /**
     * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post> $posts
     */
    public function setPosts(Collection $posts)
    {
        $this->posts = $posts;
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $post
     */
    public function addPost(Post $post)
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $post
     */
    public function removePost(Post $post)
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
        }
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $post
     * @return bool
     */
    public function hasPost(Post $post)
    {
        return $this->posts->contains($post);
    }
}
