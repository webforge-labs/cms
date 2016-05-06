<?php

namespace Webforge\CmsBundle\Model;

/**
 * Entities implementing that interface will be mapped to "WebforgeGaufretteBinary" as serializer type
 */
interface GaufretteFileInterface {

  public function getGaufretteKey();

}