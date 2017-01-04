<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\CmsBundle\Model\MediaFileInterface;

Interface MediaFileHandlerInterface {

  public function serializeToFile(MediaFileInterface $mediaFile, \stdClass $file);
}
