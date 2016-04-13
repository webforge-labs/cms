<?php

namespace Webforge\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webforge\Common\ClassUtil;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Json\JsonDecoder;
use Symfony\Component\HttpFoundation\Request;

class CommonController extends Controller {

  protected $em;

  public function setContainer(ContainerInterface $container = NULL) {
    parent::setContainer($container);

    if ($container) {
      $this->em = $container->get('doctrine.orm.entity_manager');
    }
  }

  protected function getEntityFQN($name) {
    return ClassUtil::expandNamespace($name, 'Webforge\CmsBundle\Entity');
  }

  protected function getRepository($name) {
    return $this->em->getRepository($this->getEntityFQN($name));
  }

  protected function retrieveJsonBody(Request $request) {
    $body = $request->getContent();

    if (is_array($body) || is_object($body)) {
      return $body;
    }

    $json = new JsonDecoder();

    try {
      return $json->decode((string) $body);
    } catch (\Exception $e) {
      throw new BadRequestHttpException('Invalid json message received.', 0, $e);
    }
  }
}
