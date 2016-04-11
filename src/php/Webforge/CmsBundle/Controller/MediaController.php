<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends CommonController {

  /**
   * @Route("/media")
   * @Method("GET")
   */
  public function indexAction() {
    return new JsonResponse((object) ['files'=>[]]);
  }

  /**
   * @Route("/media/dropbox")
   * @Method("POST")
   */
  public function uploadMediaFromDropboxAction(Request $request) {
    return new JsonResponse((object) ['files'=>[]]);
  }
}
