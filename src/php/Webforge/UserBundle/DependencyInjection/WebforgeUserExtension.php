<?php

namespace Webforge\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class WebforgeUserExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
               'paths' => array(
                   __DIR__.'/../Resources/views' => 'FOSUser',
               ),
           ));
    }
}
