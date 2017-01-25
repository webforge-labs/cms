<?php

namespace Webforge\CmsBundle\Media;

class File implements FileInterface {

  public $key;
  public $mimeType;
  public $isExisting;
  public $name;

  public function __construct($key, $name, $mimeType) {
    $this->key = $key;
    $this->name = $name;
    $this->mimeType = $mimeType;
  }

  public function getName() {
    return $this->name;
  }

  public function getKey() {
    return $this->key;
  }

  public function getMimeType() {
    return $this->mimeType;
  }

  public function isImage() {
    return strpos($this->mimeType, 'image') === 0;
  }
}
