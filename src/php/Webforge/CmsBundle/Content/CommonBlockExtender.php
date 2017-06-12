<?php

namespace Webforge\CmsBundle\Content;

class CommonBlockExtender implements BlockExtender {

  protected $markdowner;

  public function __construct($markdowner) {
    $this->markdowner = $markdowner;
  }

  public function extend(Array $blocks, \stdClass $context) {

    $first = TRUE;
    foreach ($blocks as $block) {
      $blockType = $context->config->getBlockType($block);

      foreach ($blockType->getProperties() as $property) {
        if ($property->hasMarkdown) {
          $block->{$property->name.'Html'} = $this->markdowner->transformMarkdown($block->{$property->name});
        }
        if ($property->hasText) {
          $block->{$property->name.'Text'} = $this->markdowner->transformText($block->{$property->name});
        }
      }
    }
  }
}
