<?php

namespace Webforge\CmsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Webforge\Symfony\FormError;

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
    $manager = $this->get('webforge.media.manager');

    return $manager->asTree();
  }

  /**
   * @Route("/media/dropbox")
   * @Method("POST")
   */
  public function uploadMediaFromDropboxAction(Request $request) {
    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);
    $manager = $this->get('webforge.media.manager');

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
   * @Route("/media/upload")
   * @Method("POST")
   */
  public function uploadMediaFromAjaxAction(Request $request) {
    $user = $this->getUser();
    $manager = $this->get('webforge.media.manager');

    $manager->beginTransaction();
    $warnings = array();
    $files = array();
    foreach ($request->files->get('files') as $uploadedFile) {
      $export = new \stdClass;

      try {
        $entity = $manager->addFile(
          $request->request->get('path'),
          $uploadedFile->getClientOriginalName(), 
          file_get_contents($uploadedFile->getPathName())
        );
  
        $manager->serializeEntity($entity, $export);
      } catch (\Webforge\CmsBundle\Media\FileAlreadyExistsException $e) {
        $warnings[] = sprintf('Die Datei %s existiert bereits und wird nicht von mir überschrieben. Du musst sie erst löschen, um sie zu ersetzen', $e->getPath());
        $manager->serializeFile($e->mediaKey, $export);
      }

      $files[] = $export;
    }
    $manager->commitTransaction();

    $data = $this->getIndex();
    $data->warnings = $warnings;
    $data->files = $files;

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
    foreach ($json->sources as $path) {
      $manager->moveByPath($path, $json->target);
    }
    $manager->commitTransaction();

    return $this->indexAction();
  }

  /**
   * @Route("/media/rename")
   * @Method("POST")
   */
  public function renameFile(Request $request) {
    $user = $this->getUser();
    $this->convertJsonToForm($request);

    $json = (object) [
      'name'=>NULL,
      'path'=>NULL
    ];

    $form = $this->get('form.factory')->createNamedBuilder(null, 'form', $json, array('csrf_protection'=>false))
      ->add('name', 'text', array(
        'constraints'=>array(new Assert\NotBlank())
      ))
      ->add('path', 'text', array(
        'constraints'=>array(new Assert\NotBlank())
      ))
      ->getForm();

    $form->bind($request);

    if ($form->isValid()) {
      $manager = $this->get('webforge.media.manager');

      $manager->beginTransaction();
      $manager->renameByPath($json->path, $json->name);
      $manager->commitTransaction();

      return $this->indexAction();
    }

    $formError = new FormError();

    return new JsonResponse(
      (object) $formError->wrap($form),
      400
    );
  }
}
