<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tree\Node\NodeInterface;
use Tree\Node\Node;

class NodeRemoverVisitor implements \Tree\Visitor\Visitor {

  private $keyToRemove;

  public function __construct($mediaKey) {
    $this->keyToRemove = $mediaKey;
  }
  
  public function visit(NodeInterface $node) {
    if ($node instanceof FileNode && $node->getMediaKey() === $this->keyToRemove) {
      $node->getParent()->removeChild($node);
      return TRUE;
    }

    foreach ($node->getChildren() as $child) {
      if ($child->accept($this)) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
