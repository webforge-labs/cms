<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use Webforge\CmsBundle\Model\GaufretteFileInterface;
use Webforge\Gaufrette\File as GaufretteFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webforge\CmsBundle\Media\Manager as MediaManager;

class JmsSerializerGaufretteBinaryHandler {

  private $manager, $serializeHandlers;

  public function __construct(MediaManager $manager, Array $handlers) {
    $this->manager = $manager;
    $this->serializeHandlers = $handlers;
  }

  public function serialize(JsonSerializationVisitor $visitor, GaufretteFileInterface $binary, array $type, Context $context)  {
    // @TODO i want to export all serialized properties here, what should i use the visitor? the context?
    // $serialized = $visitor->getNavigator()->accept($binary, $type, $context);

    $file = new \stdClass;
    $file->id = $binary->getId();
    $file->key = $binary->getGaufretteKey();

    try {
      $gaufretteFile = $this->manager->getFile($binary->getGaufretteKey());

      $this->serializeToFile($gaufretteFile, $file);
      $file->isExisting = TRUE;

    } catch (\Gaufrette\Exception\FileNotFound $e) {
      $file->isExisting = FALSE;
    }

    // we return an array here, because otherwise @inline in serializer will not work
    return (array) $file;
  }

  public function serializeToFile(GaufretteFile $gFile, \stdClass $file) {
    $file->url = '/cms/media?download=1&file='.urlencode($gFile->getRelativePath());
    $file->mimeType = $gFile->mimeType;

    foreach ($this->serializeHandlers as $handler) {
      $handler->serializeToFile($gFile, $file);
    }
  }

  public function asTree() {
    $that = $this;
    $options = [
      'withFile'=>function(GaufretteFile $gFile, \stdClass $file) use ($that) {
        return $that->serializeToFile($gFile, $file);
      }
    ];

    return (object) ['root'=>$this->manager->asTree($options)];
  }
}
