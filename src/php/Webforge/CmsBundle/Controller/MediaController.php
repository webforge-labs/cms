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
    $handler = $this->get('webforge.serialization.jms_gaufrette_binary_handler');

    return $handler->asTree();
  }

  /**
   * @Route("/media/dropbox")
   * @Method("POST")
   */
  public function uploadMediaFromDropboxAction(Request $request) {
    $manager = $this->get('webforge.media.manager');

    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);

    $manager->beginTransaction();
    $warnings = array();
    foreach ($json->dropboxFiles as $dbFile) {
      try {
        $manager->addFile($json->path, $dbFile->name, file_get_contents($dbFile->link));

      } catch (\Webforge\CmsBundle\Media\FileAlreadyExistsException $e) {
        $warnings[] = sprintf('Die Datei %s existiert bereits und wird nicht von mir überschrieben. Du musst sie erst löschen, um sie zu ersetzen', $e->getPath());
      }
    }

    $manager->commitTransaction();

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

    $manager = $this->get('webforge.media.manager');

    $manager->beginTransaction();

    foreach ($json->keys as $key) {
      $manager->deleteFileByKey($key);
    }

    $manager->commitTransaction();

    return $this->indexAction();
  }

  /**
   * @Route("/media/move")
   * @Method("POST")
   */
  public function moveFiles(Request $request) {
    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);
    $manager = $this->get('webforge.media.manager');

    $manager->beginTransaction();
    foreach ($json->keys as $key) {
      $manager->moveByKey($key, $json->target);
    }
    $manager->commitTransaction();

    return $this->indexAction();
  }
}
