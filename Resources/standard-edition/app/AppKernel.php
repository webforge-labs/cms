<?php

use Webforge\Symfony\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {
  public function registerBundles() {
    $bundles = parent::registerBundles();

    $bundles[] = new \%project.bundle_namespace%\%project.bundle_name%();

    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        //$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
    }

    return $bundles;
  }
}
