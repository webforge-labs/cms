<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\Common\Collections\Collection;
use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Author
 *
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Author class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledAuthor extends User
{
  
  /**
   * writtenPosts
   * @var Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
   * @ORM\OneToMany(mappedBy="author", targetEntity="Webforge\Doctrine\Test\Entities\Post")
   */
    protected $writtenPosts;
  
    /**
     * revisionedPosts
     * @var Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
     * @ORM\OneToMany(mappedBy="revisor", targetEntity="Webforge\Doctrine\Test\Entities\Post")
     */
    protected $revisionedPosts;
  
    public function __construct($email)
    {
        parent::__construct($email);
        $this->writtenPosts = new ArrayCollection();
        $this->revisionedPosts = new ArrayCollection();
    }
  
    /**
     * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
     */
    public function getWrittenPosts()
    {
        return $this->writtenPosts;
    }
  
    /**
     * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post> $writtenPosts
     */
    public function setWrittenPosts(Collection $writtenPosts)
    {
        $this->writtenPosts = $writtenPosts;
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $writtenPost
     */
    public function addWrittenPost(Post $writtenPost)
    {
        if (!$this->writtenPosts->contains($writtenPost)) {
            $this->writtenPosts->add($writtenPost);
        }
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $writtenPost
     */
    public function removeWrittenPost(Post $writtenPost)
    {
        if ($this->writtenPosts->contains($writtenPost)) {
            $this->writtenPosts->removeElement($writtenPost);
        }
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $writtenPost
     * @return bool
     */
    public function hasWrittenPost(Post $writtenPost)
    {
        return $this->writtenPosts->contains($writtenPost);
    }
  
    /**
     * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
     */
    public function getRevisionedPosts()
    {
        return $this->revisionedPosts;
    }
  
    /**
     * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post> $revisionedPosts
     */
    public function setRevisionedPosts(Collection $revisionedPosts)
    {
        $this->revisionedPosts = $revisionedPosts;
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $revisionedPost
     */
    public function addRevisionedPost(Post $revisionedPost)
    {
        if (!$this->revisionedPosts->contains($revisionedPost)) {
            $this->revisionedPosts->add($revisionedPost);
        }
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $revisionedPost
     */
    public function removeRevisionedPost(Post $revisionedPost)
    {
        if ($this->revisionedPosts->contains($revisionedPost)) {
            $this->revisionedPosts->removeElement($revisionedPost);
        }
        return $this;
    }
  
    /**
     * @param Webforge\Doctrine\Test\Entities\Post $revisionedPost
     * @return bool
     */
    public function hasRevisionedPost(Post $revisionedPost)
    {
        return $this->revisionedPosts->contains($revisionedPost);
    }
}
