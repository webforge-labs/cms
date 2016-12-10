<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use URLify;

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
      $filename = $this->normalizeFilename($dbFile->name);
      try {
        $filesystem->write($path.$filename, file_get_contents($dbFile->link));
      } catch (\Gaufrette\Exception\FileAlreadyExists $e) {
        $warnings[] = sprintf('Die Datei %s existiert bereits und wird nicht von mir überschrieben. Du musst sie erst löschen, um sie zu ersetzen', $path.$filename);
      }
    }

    $data = $this->getIndex();
    $data->warnings = $warnings;

    return new JsonResponse($data, 201);
  }

  private function normalizeFilename($name) {
    return URLify::filter($name, 120, 'de', $isFilanme = true);
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

  /**
   * @Route("/media/move")
   * @Method("POST")
   */
  public function moveFiles(Request $request) {
    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);

    $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('cms_media');

    foreach ($json->keys as $key) {
      if (!$filesystem->isDirectory($key)) {
        $path = explode('/', ltrim($key, '/'));
        $filename = array_pop($path);

        $filesystem->rename($key, $json->target.$filename);
      }
    }


    return $this->indexAction();
  }
}
