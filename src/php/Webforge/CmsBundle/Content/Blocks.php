<?php

namespace Webforge\CmsBundle\Content;

use Webmozart\Json\JsonDecoder;

class Blocks {

  private $config;
  private $blockTypes = array();
  private $markdowner;

  public function __construct(Array $blockExtenders, $configFile, $markdowner) {
    $this->initConfig($configFile);
    $this->blockExtenders = $blockExtenders;
    $this->markdowner = $markdowner;
  }

  /**
   * @param stdClass $contents was written from content-manager.js should have array ->blocks
   */
  public function extendContentStream(\stdClass $contents, \stdClass $context) {
    if (is_array($contents->blocks)) {

      $context->markdowner = $this->markdowner;
      $context->config = $this;

      foreach ($this->blockExtenders as $blockExtender) {
        $blockExtender->extend($contents->blocks, $context);
      }
    }
  }

  private function initConfig($configFile) {
    $decoder = new JsonDecoder();

    $this->config = $decoder->decodeFile($configFile);
  }

  public function getBlockType(\stdClass $block) {
    if (!isset($this->blockTypes[$block->type])) {
      $this->initBlockType($block->type);
    }

    return $this->blockTypes[$block->type];
  }

  private function initBlockType($name) {

    foreach ($this->config as $blockDefinition) {
      if (!isset($blockDefinition->component)) {
        $blockDefinition->component = $blockDefinition->name;
      }

      if ($blockDefinition->name == $name) {
        $properties = array();

        if (isset($blockDefinition->compounds)) {
          foreach ($blockDefinition->compounds as $compoundBlockDefinition) {
            $this->parseProperty($compoundBlockDefinition, $properties);
          }

        } else {
          // single blocks have only one property which can be renamed and defaults to the blockType->name
          $this->parseProperty($blockDefinition, $properties);
        }

        return $this->blockTypes[$blockDefinition->name] = new BlockType($blockDefinition->name, $properties);
      }
    }

    throw new \InvalidArgumentException(sprintf('Blocktype "%s" cannot be found in blocktypes config', $name));
  }

  private function parseProperty(\stdClass $blockDefinition, Array &$properties) {
    $prop = isset($blockDefinition->params) && !empty($blockDefinition->params->propertyName) ? $blockDefinition->params->propertyName : $blockDefinition->name;
    $properties[$prop] = (object) [
      'name'=>$prop,
      'component'=>$blockDefinition->component,
      'hasMarkdown'=>in_array($blockDefinition->component, array('markdown', 'textline')),
      'hasText'=>in_array($blockDefinition->component, array('textline')),
      'hasFiles'=>in_array($blockDefinition->component, array('multiple-files-chooser')),
    ];
  }

}
