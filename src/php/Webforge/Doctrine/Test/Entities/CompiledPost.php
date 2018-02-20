<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\Common\Collections\Collection;
use Webforge\Common\DateTime\DateTime;
use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Post
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Post class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledPost {
  
  /**
   * id
   * @var integer
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id;
  
  /**
   * author
   * @var Webforge\Doctrine\Test\Entities\Author
   * @ORM\ManyToOne(targetEntity="Webforge\Doctrine\Test\Entities\Author", inversedBy="writtenPosts")
   * @ORM\JoinColumn(nullable=false)
   */
  protected $author;
  
  /**
   * revisor
   * @var Webforge\Doctrine\Test\Entities\Author
   * @ORM\ManyToOne(targetEntity="Webforge\Doctrine\Test\Entities\Author", inversedBy="revisionedPosts")
   * @ORM\JoinColumn
   */
  protected $revisor;
  
  /**
   * categories
   * @var Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Category>
   * @ORM\ManyToMany(targetEntity="Webforge\Doctrine\Test\Entities\Category", inversedBy="posts")
   * @ORM\JoinTable(name="posts2categories", joinColumns={@ORM\JoinColumn(name="post_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="category_id", onDelete="cascade")})
   */
  protected $categories;
  
  /**
   * tags
   * @var Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Tag>
   * @ORM\ManyToMany(targetEntity="Webforge\Doctrine\Test\Entities\Tag")
   * @ORM\JoinTable(name="posts2tags", joinColumns={@ORM\JoinColumn(name="post_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="tag_id", onDelete="cascade")})
   */
  protected $tags;
  
  /**
   * images
   * @var Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\PostImage>
   * @ORM\OneToMany(mappedBy="post", targetEntity="Webforge\Doctrine\Test\Entities\PostImage")
   * @ORM\OrderBy({"position"="ASC"})
   */
  protected $images;
  
  /**
   * active
   * @var bool
   * @ORM\Column(type="boolean")
   */
  protected $active;
  
  /**
   * created
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime")
   */
  protected $created;
  
  /**
   * modified
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime", nullable=true)
   */
  protected $modified;
  
  public function __construct(Author $author, Author $revisor = NULL) {
    if (isset($author)) {
        $this->setAuthor($author);
    }
    if (isset($revisor)) {
        $this->setRevisor($revisor);
    }
    $this->categories = new ArrayCollection();
    $this->tags = new ArrayCollection();
    $this->images = new ArrayCollection();
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
   * @return Webforge\Doctrine\Test\Entities\Author
   */
  public function getAuthor() {
    return $this->author;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Author $author
   */
  public function setAuthor(Author $author) {
    if (isset($this->author) && $this->author !== $author) {
        $this->author->removeWrittenPost($this);
    }
    $this->author = $author;
    $author->addWrittenPost($this);
    return $this;
  }
  
  /**
   * @return Webforge\Doctrine\Test\Entities\Author
   */
  public function getRevisor() {
    return $this->revisor;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Author $revisor
   */
  public function setRevisor(Author $revisor = NULL) {
    if (isset($this->revisor) && $this->revisor !== $revisor) {
        $this->revisor->removeRevisionedPost($this);
    }
    $this->revisor = $revisor;
    if (isset($revisor)) {
        $revisor->addRevisionedPost($this);
    }
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Category>
   */
  public function getCategories() {
    return $this->categories;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Category> $categories
   */
  public function setCategories(Collection $categories) {
    $this->categories = $categories;
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Category $category
   */
  public function addCategory(Category $category) {
    if (!$this->categories->contains($category)) {
        $this->categories->add($category);
        $category->addPost($this);
    }
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Category $category
   */
  public function removeCategory(Category $category) {
    if ($this->categories->contains($category)) {
        $this->categories->removeElement($category);
        $category->removePost($this);
    }
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Category $category
   * @return bool
   */
  public function hasCategory(Category $category) {
    return $this->categories->contains($category);
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Tag>
   */
  public function getTags() {
    return $this->tags;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Tag> $tags
   */
  public function setTags(Collection $tags) {
    $this->tags = $tags;
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Tag $tag
   */
  public function addTag(Tag $tag) {
    if (!$this->tags->contains($tag)) {
        $this->tags->add($tag);
    }
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Tag $tag
   */
  public function removeTag(Tag $tag) {
    if ($this->tags->contains($tag)) {
        $this->tags->removeElement($tag);
    }
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Tag $tag
   * @return bool
   */
  public function hasTag(Tag $tag) {
    return $this->tags->contains($tag);
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\PostImage>
   */
  public function getImages() {
    return $this->images;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\PostImage> $images
   */
  public function setImages(Collection $images) {
    $this->images = $images;
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\PostImage $image
   */
  public function addImage(PostImage $image) {
    if (!$this->images->contains($image)) {
        $this->images->add($image);
    }
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\PostImage $image
   */
  public function removeImage(PostImage $image) {
    if ($this->images->contains($image)) {
        $this->images->removeElement($image);
    }
    return $this;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\PostImage $image
   * @return bool
   */
  public function hasImage(PostImage $image) {
    return $this->images->contains($image);
  }
  
  /**
   * @return bool
   */
  public function getActive() {
    return $this->active;
  }
  
  /**
   * @param bool $active
   */
  public function setActive($active) {
    $this->active = $active;
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
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getModified() {
    return $this->modified;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $modified
   */
  public function setModified(DateTime $modified = NULL) {
    $this->modified = $modified;
    return $this;
  }
}
