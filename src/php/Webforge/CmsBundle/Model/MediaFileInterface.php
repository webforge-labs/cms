<?php

namespace Webforge\CmsBundle\Model;

interface MediaFileInterface extends \Webforge\CmsBundle\Model\GaufretteFileInterface {

  public function setOriginalName($name);

  public function getOriginalName();

}
