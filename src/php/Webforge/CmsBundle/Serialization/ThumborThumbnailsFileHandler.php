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

  public function __construct($transformations, $transformer, $filesystem) {
    $this->transformer = $transformer;
    $this->transformations = $transformations;
    $this->enabled = count($this->transformations) > 0;
    $this->filesystem = $filesystem;
  }

  public function serializeToFile(FileInterface $mediaFile, MediaFileEntityInterface $entity, stdClass $file) {
    if ($this->enabled && $mediaFile->isImage()) {

      if (!isset($file->thumbnails)) {
        $file->thumbnails = [];
      } else {
        $file->thumbnails = (array) $file->thumbnails;
      }

      foreach ($this->transformations as $name => $transformation) {
        $meta = new stdClass;
        $builder = $this->transformer->transform($file->url, $name);

        if (array_key_exists('metadata_only', $transformation) && $transformation['metadata_only']) {
          $metadataUrl = (string) $builder->build();

          // build the real url without metadata
          $builder->metadataOnly(false);
          $meta->url = (string) $builder->build();

          $this->fetchAndMergeMetadata($metadataUrl, $entity, 'thumbor.'.$name, $meta, $file);
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

      /* this does not work right now, because: https://github.com/thumbor/thumbor/issues/949 
      $context = stream_context_create([
        'http'=>[
          'method'=> 'GET',
          'header'=>"Accept: application/json\r\n"
        ]
      ]);
      $metadata = json_decode(file_get_contents($url, false, $context))->thumbor;
      */
      $metadata = new \stdClass;

      $physicalFile = $this->filesystem->get($file->key);
      $size = getimagesizefromstring($physicalFile->getContent());

      if ($size) {
        $metadata->source = (object) [
          'width'=>$size[0],
          'height'=>$size[1]
        ];
      }

      $context = stream_context_create([
        'http'=>[
          'method'=> 'GET'
        ]
      ]);

      $size = getimagesizefromstring(file_get_contents($thumbnailMeta->url, false, $context));

      if (!$size) {
        throw new \Exception('cannot getimagesizefromstring for url: '.$thumbnailMeta->url);
      }

      $metadata->target = (object) [
        'width'=>$size[0],
        'height'=>$size[1]
      ];

      $entity->setMediaMetadata($cacheKey, $metadata);
    }

    $thumbnailMeta->width = $metadata->target->width;
    $thumbnailMeta->height = $metadata->target->height;
    $thumbnailMeta->isPortrait = $isPortrait = ($metadata->target->height > $metadata->target->width);
    $thumbnailMeta->isLandscape = $metadata->target->width > $metadata->target->height;
    $thumbnailMeta->orientation = $isPortrait ? 'portrait' : 'landscape'; // square === landscape

    $file->width = $metadata->source->width;
    $file->height = $metadata->source->height;
    $file->isPortrait = $isPortrait = ($metadata->source->height > $metadata->source->width);
    $file->isLandscape = $metadata->source->width > $metadata->target->height;
    $file->orientation = $isPortrait ? 'portrait' : 'landscape'; // square === landscape
  }
}
