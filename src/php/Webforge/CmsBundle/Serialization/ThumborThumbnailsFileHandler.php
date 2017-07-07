<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\CmsBundle\Media\FileInterface;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;
use RuntimeException;
use stdClass;

class ThumborThumbnailsFileHandler implements MediaFileHandlerInterface {

  protected $transformations;
  protected $transformer;
  private $enabled;

  public function __construct($transformations, $transformer) {
    $this->transformer = $transformer;
    $this->transformations = $transformations;
    $this->enabled = count($this->transformations) > 0;
  }

  public function serializeToFile(FileInterface $mediaFile, MediaFileEntityInterface $entity, stdClass $file) {
    if ($this->enabled && $mediaFile->isImage()) {

      if (!isset($file->thumbnails)) {
        $file->thumbnails = [];
      } else {
        $file->thumbnails = (array) $file->thumbnails;
      }

      foreach ($this->transformations as $name => $transformation) {
        $path = $mediaFile->getKey().'/'.$mediaFile->getName();

        $meta = new stdClass;
        $builder = $this->transformer->transform($file->url, $name);

        if (array_key_exists('metadata_only', $transformation) && $transformation['metadata_only']) {
          $metadataUrl = (string) $builder->build();
          $this->fetchAndMergeMetadata($metadataUrl, $entity, 'thumbor.'.$name, $meta, $file);

          // build the real url without metadata
          $builder->metadataOnly(false);
          $meta->url = (string) $builder->build();

        } else {
          $meta->url = (string) $builder->build();
        }

        $meta->name = $name;

        $file->thumbnails[$name] = $meta;
      }
    }
  }

  public function fetchAndMergeMetadata($url, MediaFileEntityInterface $entity, $cacheKey, stdClass $thumbnailMeta, stdClass $file) {
    $metadata = $entity->getMediaMetadata($cacheKey);

    if (!$metadata) {
      $context = stream_context_create([
        'http'=>[
          'method'=> 'GET',
          'header'=>"Accept: application/json\r\n"
        ]
      ]);

      $metadata = json_decode(file_get_contents($url, false, $context))->thumbor;
      $entity->setMediaMetadata($cacheKey, $metadata);
    }

    $thumbnailMeta->width = $metadata->target->width;
    $thumbnailMeta->height = $metadata->target->height;

    $file->width = $metadata->source->width;
    $file->height = $metadata->source->height;
  }
}
