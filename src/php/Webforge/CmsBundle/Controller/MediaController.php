<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends CommonController {

  protected $thumbnailTypes = array('xs');

  /**
   * @Route("/media")
   * @Method("GET")
   */
  public function indexAction() {
    return new JsonResponse($this->getIndex());
  }

  /**
   * @Route("/media/file-manager")
   * @Method("GET")
   */
  public function fileManagerAction() {
    return $this->render("WebforgeCmsBundle:components:file-manager-tab.html.twig", array(
      
    ));
  }

  protected function getIndex() {
    $handler = $this->get('webforge.serialization.gaufrette_binary_handler');

    return $handler->asTree();
  }

  /**
   * @Route("/media/dropbox")
   * @Method("POST")
   */
  public function uploadMediaFromDropboxAction(Request $request) {
    $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('cms_media');

    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);

    $path = trim($json->path, '/').'/'; // store without leadingslash
    $warnings = array();
    foreach ($json->dropboxFiles as $dbFile) {
      try {
        $filesystem->write($path.$dbFile->name, file_get_contents($dbFile->link));
      } catch (\Gaufrette\Exception\FileAlreadyExists $e) {
        $warnings[] = sprintf('Die Datei %s existiert bereits und wird nicht von mir überschrieben. Du musst sie erst löschen, um sie zu ersetzen', $path.$dbFile->name);
      }
    }

    $data = $this->getIndex();
    $data->warnings = $warnings;

    return new JsonResponse($data, 201);
  }

  /**
   * @Route("/media")
   * @Method("DELETE")
   */
  public function batchDeleteMediaAction(Request $request) {
    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);

    $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('cms_media');

    foreach ($json->keys as $key) {
      $filesystem->delete($key);
    }

    return $this->indexAction();
  }
}
