<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Webforge\Gaufrette\Index;

class MediaController extends CommonController {

  /**
   * @Route("/media")
   * @Method("GET")
   */
  public function indexAction() {
    $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('cms_media');

    $index = new Index($filesystem);

    return new JsonResponse((object) ['root'=>$index->asTree()]);
  }

  /**
   * @Route("/media/dropbox")
   * @Method("POST")
   */
  public function uploadMediaFromDropboxAction(Request $request) {
    $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('cms_media');

    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);

    $path = trim($json->path, '/').'/'; // store without leadingslash but with trailingslash
    foreach ($json->dropboxFiles as $dbFile) {
      $filesystem->write($path.$dbFile->name, file_get_contents($dbFile->link));
    }

    $response = $this->indexAction();
    $response->setStatusCode(201);

    return $response;
  }
}
