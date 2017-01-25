<?php

namespace Webforge\CmsBundle\Media;

class DumpVisitor implements \Tree\Visitor\Visitor {

  public function visit(\Tree\Node\NodeInterface $node) {
    
    $output = "\n".str_repeat(' ', $node->getDepth()).$node->getValue().' '.get_class($node).' '.($node->isLeaf() ? 'leaf' : '');

    foreach ($node->getChildren() as $child) {
      $output .= $child->accept($this);
    }

    return $output;
  }
}
