<?php

namespace Webforge\CmsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebforgeCmsBundle extends Bundle {

  public function getParent() {
    return 'FOSUserBundle';
  }
}
