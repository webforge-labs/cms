<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\Gaufrette\File as GaufretteFile;

Interface GaufretteBinaryFileHandler {

  public function serializeToFile(GaufretteFile $gFile, \stdClass $file);
}
