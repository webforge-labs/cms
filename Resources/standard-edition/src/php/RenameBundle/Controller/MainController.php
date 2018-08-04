<?php

namespace %project.bundle_namespace%\Controller;

use Symfony\Component\Routing\Annotation\Route;

class MainController extends \Webforge\CmsBundle\Controller\CommonController {

  /**
   * @Route("/", name="home", methods={"GET"})
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
