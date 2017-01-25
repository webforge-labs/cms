<?php

namespace Webforge\CmsBundle\Model;

/**
 * Entities implementing that interface will be mapped to a special serializer type that converts images and serializes infos for the media file
 */
interface MediaFileEntityInterface {

  public function getMediaFileKey();

  public function setMediaFileKey($key);

  public function getMediaName();

  /**
   * Sets a descriptive name - used as a filename in outputs
   * @param string $filename
   */
  public function setMediaName($filename);

}