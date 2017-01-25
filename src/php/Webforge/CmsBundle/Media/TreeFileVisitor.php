<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TreeFileVisitor implements \Tree\Visitor\Visitor {

  public function __construct(Array $options) {
    $this->options = $this->getOptionsResolver()->resolve($options);
  }
  
  protected function getOptionsResolver() {
    $resolver = new OptionsResolver();
    $resolver->setDefaults(array(
      'withFile' => function($mediaKey, \stdClass $export) {
      }
    ));

    $resolver->setAllowedTypes('withFile', 'Closure');

    return $resolver;
  }

  public function visit(\Tree\Node\NodeInterface $node) {
    $export = (object) [
      'type'=>$node->isRoot() ? 'ROOT' : ($node instanceof FileNode ? 'file' : 'directory'),
      'name'=>$node->getValue(),
      'items'=>array()
    ];
    
    foreach ($node->getChildren() as $child) {
      if ($item = $child->accept($this)) {
        $export->items[] = $item;
      }
    }

    if ($export->type === 'file') {
      $withFile = $this->options['withFile'];
      $withFile($node, $export);
    } elseif ($export->type === 'directory' && count($export->items) == 0) { // dont export empty directories
      return FALSE;
    }

    return $export;
  }
}
