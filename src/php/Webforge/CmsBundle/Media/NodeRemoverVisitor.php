<?php

namespace Webforge\CmsBundle\Media;

use Tree\Node\NodeInterface;

class NodeRemoverVisitor implements \Tree\Visitor\Visitor
{
    private $keyToRemove;

    public function __construct($mediaKey)
    {
        $this->keyToRemove = $mediaKey;
    }

    public function visit(NodeInterface $node)
    {
        if ($node instanceof FileNode && $node->getMediaKey() === $this->keyToRemove) {
            $node->getParent()->removeChild($node);
            return true;
        }

        foreach ($node->getChildren() as $child) {
            if ($child->accept($this)) {
                return true;
            }
        }

        return false;
    }
}
