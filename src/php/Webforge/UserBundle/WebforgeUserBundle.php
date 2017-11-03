<?php

namespace Webforge\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * UserBundle
 *
 * to seperate webforge from the third-party-bundle FOSUserBundle
 * this gets loaded through webforge-symfony-Kernel
 */
class WebforgeUserBundle extends Bundle {

  public function getParent() {
    return 'FOSUserBundle';
  }
}
