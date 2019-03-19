<?php

namespace Webforge\CmsBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Webforge\CmsBundle\JobDispatcherInterface;
use Webforge\CmsBundle\Media\Manager;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;
use Webforge\Doctrine\Entities;
use Webforge\Symfony\FormError;

class MediaController extends CommonController
{
    /**
     * @var Manager
     */
    private $mediaManager;

    /**
     * @var JobDispatcherInterface
     */
    private $jobDispatcher;

    public function __construct(EntityManagerInterface $em, Entities $dc, Manager $mediaManager, JobDispatcherInterface $jobDispatcher)
    {
        parent::__construct($em, $dc);
        $this->mediaManager = $mediaManager;
        $this->jobDispatcher = $jobDispatcher;
    }

    /**
     * @Route("/media", methods={"GET"}, defaults={"_format": "json"})
     */
    public function indexAction(Request $request)
    {
        $response = new JsonResponse(
            $this->getIndex([
                'filters' => $request->query->all()
            ])
        );

        // binary metadata might be created, that needs to be persisted
        $this->em->flush();

        return $response;
    }

    /**
     * @Route("/media/file-manager", methods={"GET"})
     */
    public function fileManagerAction()
    {
        return $this->render("@WebforgeCms/components/file-manager-tab.html.twig", array());
    }

    protected function getIndex(array $options = array())
    {
        return $this->mediaManager->asTree($options);
    }

    /**
     * @Route("/media/dropbox", methods={"POST"}, defaults={"_format": "json"})
     */
    public function uploadMediaFromDropboxAction(Request $request)
    {
        $json = $this->retrieveJsonBody($request);
        /** @var Manager $manager */
        $manager = $this->mediaManager;

        $manager->beginTransaction();
        $warnings = array();
        $entities = [];
        foreach ($json->dropboxFiles as $dbFile) {
            $wasUpdated = null;

            $entities[] = $manager->addOrUpdateFile(
                $folder = $json->path,
                $name = $dbFile->name,
                file_get_contents($dbFile->link),
                $wasUpdated
            );

            if ($wasUpdated) {
                $warnings[] = sprintf(
                    'Die Datei %s existierte bereits wurde überschrieben',
                    $manager->getNormalizedPath($folder, $name)
                );
            }
        }

        $manager->commitTransaction();

        foreach ($entities as $entity) {
            $this->warmupSerializationInBackground($entity);
        }

        $data = $this->getIndex(['filters' => $request->query->all()]);
        $data->warnings = $warnings;

        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/media/upload", methods={"POST"}, defaults={"_format": "json"})
     */
    public function uploadMediaFromAjaxAction(Request $request)
    {
        /** @var Manager $manager */
        $manager = $this->mediaManager;

        $options = ['filters' => $request->query->all()];

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
            $wasUpdated = null;

            $entities[] = $entity = $manager->addOrUpdateFile(
                $folder = $request->request->get('path'),
                $name = $uploadedFile->getClientOriginalName(),
                file_get_contents($uploadedFile->getPathName()),
                $wasUpdated
            );

            $manager->serializeEntity($entity, $export, $options);

            if ($wasUpdated) {
                $warnings[] = sprintf(
                    'Die Datei %s existierte bereits und wurde überschrieben.',
                    $manager->getNormalizedPath($folder, $name)
                );
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
     * @Route("/media", methods={"DELETE"})
     */
    public function batchDeleteMediaAction(Request $request)
    {
        $json = $this->retrieveJsonBody($request);

        $manager = $this->mediaManager;

        $manager->beginTransaction();

        foreach ($json->keys as $key) {
            $manager->deleteFileByKey($key);
        }

        $manager->commitTransaction();

        return $this->indexAction($request);
    }

    /**
     * @Route("/media/move", methods={"POST"})
     */
    public function moveFiles(Request $request)
    {
        $json = $this->retrieveJsonBody($request);
        $manager = $this->mediaManager;

        $manager->beginTransaction();
        foreach ($json->sources as $path) {
            $manager->moveByPath($path, $json->target);
        }
        $manager->commitTransaction();

        return $this->indexAction($request);
    }

    /**
     * @Route("/media/rename", methods={"POST"}, defaults={"_format": "json"})
     */
    public function renameFile(Request $request)
    {
        $this->convertJsonToForm($request);

        $json = (object)[
            'name' => null,
            'path' => null
        ];

        $form = $this->get('form.factory')->createNamedBuilder(
            null,
            FormType::class,
            $json,
            array('csrf_protection' => false)
        )
            ->add('name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank())
            ))
            ->add('path', TextType::class, array(
                'constraints' => array(new Assert\NotBlank())
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->mediaManager;

            $manager->beginTransaction();
            $manager->renameByPath($json->path, $json->name);
            $manager->commitTransaction();

            return $this->indexAction($request);
        }

        $formError = new FormError();

        return new JsonResponse(
            (object)$formError->wrap($form),
            400
        );
    }

    protected function warmupSerializationInBackground(MediaFileEntityInterface $entity)
    {
        $this->jobDispatcher->dispatchCliCommand('cms:warmup-media-file', [$entity->getMediaFileKey()]);
    }
}
