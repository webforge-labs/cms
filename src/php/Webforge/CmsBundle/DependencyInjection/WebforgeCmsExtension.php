<?php

namespace Webforge\CmsBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class WebforgeCmsExtension extends Extension implements PrependExtensionInterface, CompilerPassInterface {

  public function load(array $configs, ContainerBuilder $container) {
    $loader = new YamlFileLoader(
      $container,
      new FileLocator(__DIR__.'/../Resources/config')
    );

    $loader->load('cms-services.yml');
  }
  
  public function prepend(ContainerBuilder $container) {
    // special handling for the liip_imagine extension that is very weird
    $loader = new YamlFileLoader(
      $container,
      new FileLocator(__DIR__.'/../Resources/config')
    );
    $loader->load('parts/imagine.yml');

    // make user dynamic
    $container->prependExtensionConfig('fos_user', array('user_class'=>$container->getParameter('entities_namespace').'\\User'));
  }

  public function process(ContainerBuilder $container) {
    if (!$container->has('webforge_symfony_alice_loader')) {
      return;
    }

    $definition = $container->findDefinition('webforge_symfony_alice_loader');
    $taggedServices = $container->findTaggedServiceIds('webforge_cms.alice_fixtures_provider');

    foreach ($taggedServices as $id => $tags) {
      $definition->addMethodCall('addProvider', array(new Reference($id)));
    }
  }
}
