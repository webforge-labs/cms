<?php

namespace Webforge\CmsBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class WebforgeCmsExtension extends Extension {

  public function load(array $configs, ContainerBuilder $container) {
    $loader = new YamlFileLoader(
      $container,
      new FileLocator(__DIR__.'/../Resources/config')
    );

    $loader->load($container->getParameter('root_directory').'/vendor/webforge/symfony/Resources/config/services.yml');
    $loader->load('cms-services.yml');
  }
}
