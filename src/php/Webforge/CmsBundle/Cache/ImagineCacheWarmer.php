<?php

namespace Webforge\CmsBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ImagineCacheWarmer implements CacheWarmerInterface {

  protected $binaryHandler;

  public function __construct($binaryHandler) {
    $this->binaryHandler = $binaryHandler;
  }

  public function warmUp($cacheDir) {
    $this->binaryHandler->asTree();
  }

  public function isOptional() {
    return true;
  }
}
