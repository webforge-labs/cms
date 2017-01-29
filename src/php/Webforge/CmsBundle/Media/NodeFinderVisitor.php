<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\OptionsResolver\OptionsResolver;

class NodeFinderVisitor implements \Tree\Visitor\Visitor {

  private $path;
  private $toVisit;
  private $lastNode;

  public function __construct($path) {
    $tpath = trim($path, '/');

    if ($tpath == "") {
      $this->path = array();
    } else {
      $this->path = explode('/', $tpath);
    }
  }
  
  public function visit(\Tree\Node\NodeInterface $node) {
    if (!isset($this->toVisit)) {
      $this->toVisit = $this->path;
      $this->lastNode = $node;
    }

    // we found every part of the path to get to $node
    if (count($this->toVisit) == 0) return $node;

    // if one of our children is the next part to find on path, iterate
    foreach ($node->getChildren() as $child) {
      if ($child->getValue() === $this->toVisit[0]) {
        $this->lastNode = $child;
        array_shift($this->toVisit);

        if ($result = $child->accept($this)) {
          return $result;
        }
      }
    }
  }

  public function getLastNode() {
    return $this->lastNode;
  }
}
