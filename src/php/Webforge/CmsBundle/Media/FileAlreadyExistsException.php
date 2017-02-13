<?php

namespace Webforge\CmsBundle\Media;

class FileAlreadyExistsException extends \Webforge\Common\Exception {

  public $path;
  public $mediaKey;

  public function getPath() {
    return $this->path;
  }
}
