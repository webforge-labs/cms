<?php

namespace %project.bundle_namespace%\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MainController extends \Webforge\CmsBundle\Controller\CommonController {

  /**
   * @Route("/", name="home")
   * @Method("GET")
   */
  public function homeAction() {
    $site = $this->get('site');

    return $this->render('%project.bundle_name%:web:home.html.twig', array(
      'page'=>[ 
        'title' => $site->getPageTitle('Homepage'),
        'description' => 'The Homepage shows things'
      ],
      'sayhi'=>'heyho'
    ));
  }
}
