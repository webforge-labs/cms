<?php

namespace Webforge\CmsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Webforge\Common\DateTime\DateTime;
use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * Compiled Entity for Webforge\CmsBundle\Entity\NavigationNode
 * 
 * To change table name or entity repository edit the Webforge\CmsBundle\Entity\NavigationNode class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledNavigationNode {
  
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
   * title
   * @var string
   * @ORM\Column
   * @Serializer\Expose
   * @Serializer\Type("string")
   */
  protected $title;
  
  /**
   * slug
   * @var string
   * @ORM\Column
   * @Serializer\Expose
   * @Serializer\Type("string")
   */
  protected $slug;
  
  /**
   * depth
   * @var integer
   * @ORM\Column(type="integer")
   * @Serializer\Expose
   * @Serializer\Type("integer")
   */
  protected $depth;
  
  /**
   * lft
   * @var integer
   * @ORM\Column(type="integer")
   * @Serializer\Expose
   * @Serializer\Type("integer")
   */
  protected $lft;
  
  /**
   * rgt
   * @var integer
   * @ORM\Column(type="integer")
   * @Serializer\Expose
   * @Serializer\Type("integer")
   */
  protected $rgt;
  
  /**
   * children
   * @var Doctrine\Common\Collections\Collection<Webforge\CmsBundle\Entity\NavigationNode>
   * @ORM\OneToMany(mappedBy="parent", targetEntity="Webforge\CmsBundle\Entity\NavigationNode")
   * @ORM\OrderBy({"lft"="ASC"})
   * @Serializer\Type("ArrayCollection")
   * @Serializer\Expose
   * @Serializer\Type("ArrayCollection")
   */
  protected $children;
  
  /**
   * parent
   * @var Webforge\CmsBundle\Entity\NavigationNode
   * @ORM\ManyToOne(targetEntity="Webforge\CmsBundle\Entity\NavigationNode", inversedBy="children")
   * @ORM\JoinColumn
   * @Serializer\Expose
   * @Serializer\Type("Webforge\CmsBundle\Entity\NavigationNode")
   */
  protected $parent;
  
  /**
   * created
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime")
   * @Serializer\Expose
   * @Serializer\Type("WebforgeDateTime")
   */
  protected $created;
  
  /**
   * updated
   * @var Webforge\Common\DateTime\DateTime
   * @ORM\Column(type="WebforgeDateTime", nullable=true)
   * @Serializer\Expose
   * @Serializer\Type("WebforgeDateTime")
   */
  protected $updated;
  
  public function __construct() {
    $this->children = new ArrayCollection();
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
  public function getTitle() {
    return $this->title;
  }
  
  /**
   * @param string $title
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getSlug() {
    return $this->slug;
  }
  
  /**
   * @param string $slug
   */
  public function setSlug($slug) {
    $this->slug = $slug;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getDepth() {
    return $this->depth;
  }
  
  /**
   * @param integer $depth
   */
  public function setDepth($depth) {
    $this->depth = $depth;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getLft() {
    return $this->lft;
  }
  
  /**
   * @param integer $lft
   */
  public function setLft($lft) {
    $this->lft = $lft;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getRgt() {
    return $this->rgt;
  }
  
  /**
   * @param integer $rgt
   */
  public function setRgt($rgt) {
    $this->rgt = $rgt;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\CmsBundle\Entity\NavigationNode>
   */
  public function getChildren() {
    return $this->children;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\CmsBundle\Entity\NavigationNode> $children
   */
  public function setChildren(Collection $children) {
    $this->children = $children;
    return $this;
  }
  
  /**
   * @param Webforge\CmsBundle\Entity\NavigationNode $child
   */
  public function addChild(NavigationNode $child) {
    if (!$this->children->contains($child)) {
        $this->children->add($child);
        $child->addChild($this);
    }
    return $this;
  }
  
  /**
   * @param Webforge\CmsBundle\Entity\NavigationNode $child
   */
  public function removeChild(NavigationNode $child) {
    if ($this->children->contains($child)) {
        $this->children->removeElement($child);
        $child->removeChild($this);
    }
    return $this;
  }
  
  /**
   * @param Webforge\CmsBundle\Entity\NavigationNode $child
   * @return bool
   */
  public function hasChild(NavigationNode $child) {
    return $this->children->contains($child);
  }
  
  /**
   * @return Webforge\CmsBundle\Entity\NavigationNode
   */
  public function getParent() {
    return $this->parent;
  }
  
  /**
   * @param Webforge\CmsBundle\Entity\NavigationNode $parent
   */
  public function setParent(NavigationNode $parent = NULL) {
    if (isset($this->parent) && $this->parent !== $parent) {
        $this->parent->removeChild($this);
    }
    $this->parent = $parent;
    if (isset($parent)) {
        $parent->addChild($this);
    }
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
  public function getUpdated() {
    return $this->updated;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $updated
   */
  public function setUpdated(DateTime $updated = NULL) {
    $this->updated = $updated;
    return $this;
  }
}
