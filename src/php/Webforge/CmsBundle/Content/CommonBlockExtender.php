<?php

namespace Webforge\CmsBundle\Content;

class CommonBlockExtender implements BlockExtender {

  protected $markdowner;
  protected $mediaManager;

  public function __construct($markdowner, $mediaManager) {
    $this->markdowner = $markdowner;
    $this->mediaManager = $mediaManager;
  }

  public function extend(Array $blocks, \stdClass $context) {

    $first = TRUE;
    foreach ($blocks as $block) {
      $blockType = $context->config->getBlockType($block);

      foreach ($blockType->getProperties() as $property) {
        $value = isset($block->{$property->name}) ? $block->{$property->name} : NULL;

        if ($property->hasMarkdown) {
          $block->{$property->name.'Html'} = $this->markdowner->transformMarkdown($value);
        }

        if ($property->hasText) {
          $block->{$property->name.'Text'} = $this->markdowner->transformText($value);
        }

        if ($property->hasFiles) {
          // create a fresh serialized version of the file only by media key
          $newValue = array();
          foreach ($value as $fileSpec) {
            // we will overwrite a lot from $fileSpec here, regenerating thumbnail-informations, etc
            $this->mediaManager->serializeFile($fileSpec->key, $fileSpec);
            $newValue[] = $fileSpec;
          }
          // replace with fresh serialized
          $block->{$property->name} = $newValue;
        }
      }
    }
  }
}
