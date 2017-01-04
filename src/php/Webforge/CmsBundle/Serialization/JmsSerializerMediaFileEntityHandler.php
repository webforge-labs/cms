<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use Webforge\CmsBundle\Model\MediaFileInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webforge\CmsBundle\Media\Manager as MediaManager;

class JmsSerializerMediaFileEntityHandler {

  private $manager;

  public function __construct(MediaManager $manager) {
    $this->manager = $manager;
  }

  public function serialize(JsonSerializationVisitor $visitor, GaufretteFileInterface $binary, array $type, Context $context)  {
    // @TODO i want to export all serialized properties here, what should i use the visitor? the context?
    // $serialized = $visitor->getNavigator()->accept($binary, $type, $context);

    $file = new \stdClass;
    $file->id = $binary->getId();
    $file->key = $binary->getGaufretteKey();

    try {
      $mediaFile = $this->manager->getFile($binary->getGaufretteKey());

      $this->manager->serializeFile($mediaFile, $file);
      $file->isExisting = TRUE;

    } catch (\Webforge\CmsBundle\Media\FileNotFoundException $e) {
      $file->isExisting = FALSE;
    }

    // we return an array here, because otherwise @inline in serializer will not work
    return (array) $file;
  }
}
