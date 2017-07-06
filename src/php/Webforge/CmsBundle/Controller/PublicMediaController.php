<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints as Assert;
use Webforge\Symfony\FormError;

class PublicMediaController extends CommonController {

  /**
   * @Route("/media/{key}/{name}", name="public_media_original")
   * @Method("GET")
   */
  public function downloadAction($key, $name) {
    $file = $this->get('webforge.media.filesystem')->get($key);

    $response = new Response($file->getContent());

    $disposition = $response->headers->makeDisposition(
      ResponseHeaderBag::DISPOSITION_ATTACHMENT,
      $name
    );

    $response->headers->set('Content-Disposition', $disposition);

    return $response;
  }
}
