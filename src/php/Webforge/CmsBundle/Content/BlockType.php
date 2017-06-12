<?php

namespace Webforge\CmsBundle\Content;

class BlockType {

  private $properties;

  public function __construct($name, Array $properties) {
    $this->properties = $properties;
  }

  public function getProperties() {
    return $this->properties;
  }
}
