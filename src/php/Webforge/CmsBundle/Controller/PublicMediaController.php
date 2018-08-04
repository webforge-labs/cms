<?php

namespace Webforge\CmsBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PublicMediaController extends CommonController
{

    /**
     * @Route("/media/{key}/{name}", name="public_media_original", methods={"GET"})
     * @param $key
     * @param $name
     * @return Response
     */
    public function downloadAction($key, $name)
    {
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
