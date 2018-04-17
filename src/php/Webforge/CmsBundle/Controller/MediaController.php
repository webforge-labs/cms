<?php

namespace Webforge\CmsBundle\Controller;

use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Webforge\CmsBundle\Media\FileAlreadyExistsException;
use Webforge\CmsBundle\Media\Manager;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;
use Webforge\Symfony\FormError;

class MediaController extends CommonController {

  /**
   * @Route("/media")
   * @Method("GET")
   */
  public function indexAction(Request $request) {
    return new JsonResponse(
        $this->getIndex([
            'filters'=>$request->query->all()
        ])
    );
  }

  /**
   * @Route("/media/file-manager")
   * @Method("GET")
   */
  public function fileManagerAction() {
    return $this->render("WebforgeCmsBundle:components:file-manager-tab.html.twig", array(
      
    ));
  }

  protected function getIndex(Array $options = array()) {
    $manager = $this->get('webforge.media.manager');

    return $manager->asTree($options);
  }

  /**
   * @Route("/media/dropbox")
   * @Method("POST")
   */
  public function uploadMediaFromDropboxAction(Request $request) {
    $user = $this->getUser();
    $json = $this->retrieveJsonBody($request);
    /** @var Manager $manager */
    $manager = $this->get('webforge.media.manager');

    $manager->beginTransaction();
    $warnings = array();
    $entities = [];
    foreach ($json->dropboxFiles as $dbFile) {
      try {
        $entities[] = $manager->addFile($json->path, $dbFile->name, file_get_contents($dbFile->link));
      } catch (FileAlreadyExistsException $e) {
        $warnings[] = sprintf('Die Datei %s existiert bereits und wird nicht von mir überschrieben. Du musst sie erst löschen, um sie zu ersetzen', $e->getPath());
      }
    }

    $manager->commitTransaction();

    foreach ($entities as $entity) {
      $this->warmupSerializationInBackground($entity);
    }

    $data = $this->getIndex(['filters'=>$request->query->all()]);
    $data->warnings = $warnings;

    return new JsonResponse($data, 201);
  }

  /**
   * @Route("/media/upload")
   * @Method("POST")
   */
  public function uploadMediaFromAjaxAction(Request $request) {
    $user = $this->getUser();
    /** @var Manager $manager */
    $manager = $this->get('webforge.media.manager');

    $options = ['filters'=>$request->query->all()];

    $manager->beginTransaction();
    $warnings = [];
    $files = [];
    $entities = [];
    $uploadedFiles = $request->files->get('files');

    if (!$uploadedFiles) {
        throw new UploadException('The max file size seems to be hit');
    }

    foreach ($uploadedFiles as $uploadedFile) {
      $export = new \stdClass;

      try {
        $entities[] = $entity = $manager->addFile(
          $request->request->get('path'),
          $uploadedFile->getClientOriginalName(), 
          file_get_contents($uploadedFile->getPathName())
        );

        $manager->serializeEntity($entity, $export, $options);
      } catch (FileAlreadyExistsException $e) {
        $warnings[] = sprintf('Die Datei %s existiert bereits und wird nicht von mir überschrieben. Du musst sie erst löschen, um sie zu ersetzen', $e->getPath());
        $manager->serializeFile($e->mediaKey, $export, $options);
      }

      $files[] = $export;
    }
    $manager->commitTransaction();

    foreach ($entities as $entity) {
      $this->warmupSerializationInBackground($entity);
    }

    $data = new \stdClass;
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

    return $this->indexAction($request);
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

    return $this->indexAction($request);
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

    $form = $this->get('form.factory')->createNamedBuilder(null, FormType::class, $json, array('csrf_protection'=>false))
      ->add('name', TextType::class, array(
        'constraints'=>array(new Assert\NotBlank())
      ))
      ->add('path', TextType::class, array(
        'constraints'=>array(new Assert\NotBlank())
      ))
      ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $manager = $this->get('webforge.media.manager');

      $manager->beginTransaction();
      $manager->renameByPath($json->path, $json->name);
      $manager->commitTransaction();

      return $this->indexAction($request);
    }

    $formError = new FormError();

    return new JsonResponse(
      (object) $formError->wrap($form),
      400
    );
  }

    protected function warmupSerializationInBackground(MediaFileEntityInterface $entity)
    {
        try {
            $dispatcher = $this->get('job_dispatcher');

            $dispatcher->dispatchCliCommand('cms:warmup-media-file', [$entity->getMediaFileKey()]);

        } catch (NotFoundExceptionInterface $e) {
            print 'Not possible to warmup media file in background because no service "job_dispatcher" is registered';
        }
    }
}
