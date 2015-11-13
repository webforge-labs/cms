<?php

namespace Webforge\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webforge\Common\ClassUtil;

class CommonController extends Controller {

  protected $em;

  public function setContainer(ContainerInterface $container = NULL) {
    parent::setContainer($container);

    if ($container) {
      $this->em = $container->get('doctrine.orm.entity_manager');
    }
  }

  protected function getRepository($name) {
    return $this->em->getRepository(ClassUtil::expandNamespace($name, 'Webforge\CmsBundle\Entity'));
  }
}
