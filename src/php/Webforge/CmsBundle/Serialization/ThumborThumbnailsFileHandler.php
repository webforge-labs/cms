<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\CmsBundle\Media\FileInterface as MediaFileInterface;
use RuntimeException;
use stdClass;

class ThumborThumbnailsFileHandler implements MediaFileHandlerInterface {

  protected $thumbnailFilters;
  protected $transformer;

  public function __construct($transformations, $transformer) {
    $this->transformer = $transformer;
    $this->thumbnailFilters = array_keys($transformations);
  }

  public function serializeToFile(MediaFileInterface $mediaFile, stdClass $file) {
    if ($mediaFile->isImage()) {

      $file->thumbnails = [];

      foreach ($this->thumbnailFilters as $filter) {
        $path = $mediaFile->getKey().'/'.$mediaFile->getName();

        $meta = new stdClass;
        $meta->url = (string) $this->transformer->transform($file->url, $filter)->build();
        $meta->name = $filter;

        $file->thumbnails[$filter] = $meta;
      }
    }
  }
}
