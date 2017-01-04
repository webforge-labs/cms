<?php

namespace Webforge\CmsBundle\Media;

/**
 * Represents a (physical) File managed by the media manager
 *
 * Files can be stored in arbitrary file-storages, but will always be represented through this class
 */
interface FileInterface {

  /**
   * A immutable identifier for the file within it's whole lifetime
   * @return string
   */
  public function getKey();

  public function getMimeType();

  public function isImage();
}
