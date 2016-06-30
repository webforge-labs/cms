<?php

namespace Webforge\CmsBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class WebforgeCmsExtension extends Extension implements PrependExtensionInterface {

  public function load(array $configs, ContainerBuilder $container) {
    $loader = new YamlFileLoader(
      $container,
      new FileLocator(__DIR__.'/../Resources/config')
    );

    $loader->load($container->getParameter('root_directory').'/vendor/webforge/symfony/Resources/config/services.yml');
    $loader->load('cms-services.yml');
  }
  
  public function prepend(ContainerBuilder $container) {
    $loader = new YamlFileLoader(
      $container,
      new FileLocator(__DIR__.'/../Resources/config')
    );

    $loader->load('vendors.yml');
    
    // make user dynamic
    $container->prependExtensionConfig('fos_user', array('user_class'=>$container->getParameter('entities_namespace').'\\User'));
  }
}
