<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Webforge\CmsBundle\Model\GaufretteFileInterface;
use Webforge\Gaufrette\Index as GaufretteIndex;
use Webforge\Gaufrette\File as GaufretteFile;

class GaufretteBinaryHandler {

  private $filesystem, $cacheManager;
  protected $thumbnailTypes;

  public function __construct($filesystemMap, $filesystemName, $cacheManager, $filterConfiguration) {
    $this->filesystem = $filesystemMap->get($filesystemName);
    $this->index = new GaufretteIndex($this->filesystem);
    $this->cacheManager = $cacheManager;

    $this->thumbnailTypes = array();
    foreach ($filterConfiguration->all() as $key=>$filter) {
      if (isset($filter['filters']['thumbnail'])) {
        $this->thumbnailTypes[] = $key;
      }
    }
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

    return $file;
  }

  public function serializeToFile(GaufretteFile $gFile, \stdClass $file) {
    $file->url = '/cms/media?download=1&file='.urlencode($gFile->getRelativePath());
    $file->mimeType = $gFile->mimeType;
    $file->thumbnails = [];
    foreach ($this->thumbnailTypes as $thumbnailType) {
      $file->thumbnails[$thumbnailType] = (object) [
        'url'=>$this->cacheManager->getBrowserPath($gFile->getRelativePath(), $thumbnailType),
        'name'=>$thumbnailType
      ];
    }
  }

  public function asTree() {
    $that = $this;
    $options = [
      'withFile'=>function(GaufretteFile $gFile, \stdClass $file) use ($that) {
        return $that->serializeToFile($gFile, $file);
      }
    ];

    return(object) ['root'=>$this->index->asTree($options)];
  }
}
