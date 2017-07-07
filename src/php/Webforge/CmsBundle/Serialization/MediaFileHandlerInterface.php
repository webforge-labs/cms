<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\CmsBundle\Media\FileInterface as MediaFileInterface;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;

Interface MediaFileHandlerInterface {

  public function serializeToFile(MediaFileInterface $mediaFile, MediaFileEntityInterface $entity, \stdClass $file);
}
