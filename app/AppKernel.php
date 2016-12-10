<?php

use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends \Webforge\Symfony\Kernel {

  public function registerBundles() {
    $bundles = parent::registerBundles();
    $bundles[] = new \AppBundle\AppBundle();

    return $bundles;
  }
}
