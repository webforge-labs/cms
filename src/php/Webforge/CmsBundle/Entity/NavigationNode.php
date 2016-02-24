<?php

namespace Webforge\CmsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Webforge\Common\DateTime\DateTime;
use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * 
 * 
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\Entity
 * @ORM\Table(name="navigation_nodes")
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="Webforge\CmsBundle\Entity\NavigationNodeRepository")
 */
class NavigationNode extends CompiledNavigationNode {

  public function export() {
    return (object) array(
      'id'=>$this->getId(),
      'title'=>$this->getTitle()
    );
  }

  public function exportWithChildren() {
    return (object) array(
      'id'=>$this->getId(),
      'title'=>$this->getTitle(),
      'parent'=>($parent = $this->getParent()) ? $parent->export() : NULL,
      'children'=>array_map(
        function ($child) {
          return $child->exportWithChildren();
        },
        $this->getChildren()->toArray()
      )
    );
  }

  public function generateSlugs() {

  }

  public function isNew() {
    return $this->id <= 0;
  }
}
