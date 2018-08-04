<?php

namespace Webforge\CmsBundle\Media;

class DumpVisitor implements \Tree\Visitor\Visitor
{
    public function __construct($advanced = true)
    {
        $this->advanced = $advanced;
    }

    public function visit(\Tree\Node\NodeInterface $node)
    {
        $output = "\n".str_repeat(' ', $node->getDepth()).$node->getValue();

        if ($this->advanced) {
            $output .= ' '.get_class($node).' '.($node->isLeaf() ? 'leaf' : '');
        }

        foreach ($node->getChildren() as $child) {
            $output .= $child->accept($this);
        }

        return $output;
    }
}
