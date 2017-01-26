<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tree\Node\NodeInterface;
use Tree\Node\Node;

/**
 * Finds or creates the path to the node
 */
class NodePathCreaterVisitor implements \Tree\Visitor\Visitor {

  private $path;
  private $toVisit;
  private $lastNode;
  private $nodeToAdd;

  public function __construct($path) {
    $tpath = trim($path, '/');

    if ($tpath == "") {
      $this->path = array();
    } else {
      $this->path = explode('/', $tpath);
    }
  }
  
  public function visit(NodeInterface $node) {
    if (!isset($this->toVisit)) {
      $this->toVisit = $this->path;
      $this->lastNode = $node;
    }

    if (count($this->toVisit) > 0) {

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

      // we haven't found a part of the path, so we need to construct all parts left, not visited
      $nextNode = $node;
      foreach ($this->toVisit as $childValue) {
        $nextNode->addChild($newNode = new Node($childValue));
        $nextNode = $newNode;
      }
      $node = $nextNode;
    }

    // we found or created every part of the path to get to $node
    return $node;
  }

  public function getLastNode() {
    return $this->lastNode;
  }
}
