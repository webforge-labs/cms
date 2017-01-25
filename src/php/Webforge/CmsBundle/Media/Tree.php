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

  public function asScalar(Array $options) {
    $visitor = new TreeFileVisitor($options);

    return (object) ['root'=>$this->root->accept($visitor)];
  }

  public function dump() {
    return $this->root->accept(new DumpVisitor);
  }
}
