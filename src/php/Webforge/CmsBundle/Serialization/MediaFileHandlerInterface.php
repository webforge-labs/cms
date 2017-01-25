<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\CmsBundle\Media\FileInterface as MediaFileInterface;

Interface MediaFileHandlerInterface {

  public function serializeToFile(MediaFileInterface $mediaFile, \stdClass $file);
}
