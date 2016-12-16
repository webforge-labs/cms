<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use Webforge\CmsBundle\Model\GaufretteFileInterface;
use Webforge\Gaufrette\Index as GaufretteIndex;
use Webforge\Gaufrette\File as GaufretteFile;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JmsSerializerGaufretteBinaryHandler {

  private $filesystem, $index, $serializeHandlers;

  public function __construct(FilesystemMap $filesystemMap, $filesystemName, Array $handlers) {
    $this->filesystem = $filesystemMap->get($filesystemName);
    $this->index = new GaufretteIndex($this->filesystem);
    $this->serializeHandlers = $handlers;
  }

  public function serialize(JsonSerializationVisitor $visitor, GaufretteFileInterface $binary, array $type, Context $context)  {
    // @TODO i want to export all serialized properties here, what should i use the visitor? the context?
    // $serialized = $visitor->getNavigator()->accept($binary, $type, $context);

    $file = new \stdClass;
    $file->id = $binary->getId();
    $file->key = $binary->getGaufretteKey();

    try {
      $gaufretteFile = $this->index->getFile($binary->getGaufretteKey());

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

    return (object) ['root'=>$this->index->asTree($options)];
  }
}
