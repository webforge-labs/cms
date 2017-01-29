<?php

namespace Webforge\CmsBundle\Media;

use Webforge\Common\ArrayUtil as A;
use Tree\Node\Node;

class Tree {

  protected $root;

  public static function createEmpty() {
    return new static(new Node('root'));
  }

  public function __construct(\Tree\Node\NodeInterface $root) {
    $this->root = $root;
  }

  public function addNode($path, $name, $mediaKey) {
    $adder = new NodeAdderVisitor($path, new FileNode($name, $mediaKey));
    $this->root->accept($adder);
  }

  public function removeNodeByKey($mediaKey) {
    $remover = new NodeRemoverVisitor($mediaKey);
    $this->root->accept($remover);
  }

  public function moveNode($sourcePath, $targetPath) {
    $sourceNode = $this->root->accept(new NodeFinderVisitor($sourcePath));

    if (!$sourceNode) {
      throw new \LogicException('Node with path: "'.$sourcePath.'" is not existing and cannot be moved.');
    }

    $targetNode = $this->root->accept(new NodePathCreaterVisitor($targetPath));

    // if we move a node to a $targetNode where a child with $node->getValue() is already existing, we will integrate the folders
    // it is possible that children from targetNode match with children from $node as well
    $this->moveIntegrated($targetNode, $sourceNode);
  }

  protected function moveIntegrated(\Tree\Node\NodeInterface $targetNode, \Tree\Node\NodeInterface $sourceNode) {
    // $targetNode = root
    // $targetChild = root/minis

    $integration = FALSE;
    foreach ($targetNode->getChildren() as $targetChild) {
      // if one node is already existing in target, it needs to have all its children integrated as well
      if ($targetChild->getValue() === $sourceNode->getValue()) {
        $integration = TRUE;
        foreach ($sourceNode->getChildren() as $sourceChild) {
          $this->moveIntegrated($targetChild, $sourceChild);
        }
      }
    }

    // remove from old location
    $sourceNode->getParent()->removeChild($sourceNode);

    if (!$integration) {
      // move into new location
      $targetNode->addChild($sourceNode);
    }
  }

  protected function hasChild(\Tree\Node\NodeInterface $node, $childValue) {
    foreach ($node->getChildren() as $child) {
      if ($child->getValue() === $childValue) {
        return $child;
      }
    }

    return FALSE;
  }

  public function asScalar(Array $options) {
    $visitor = new TreeFileVisitor($options);

    return (object) ['root'=>$this->root->accept($visitor)];
  }

  public function dump($advanced = TRUE) {
    return $this->root->accept(new DumpVisitor($advanced));
  }
}
