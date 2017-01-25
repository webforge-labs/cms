<?php

namespace Webforge\CmsBundle\Media;

class FileNode extends \Tree\Node\Node {

  public $mediaKey;

  public function __construct($name, $mediaKey) {
    $this->mediaKey = $mediaKey;
    parent::__construct($name);
  }

  public function getMediaKey() {
    return $this->mediaKey;
  }
}
