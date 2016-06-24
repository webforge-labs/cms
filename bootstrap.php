<?php

use Webforge\Common\System\Dir;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/vendor/autoload.php';

return Webforge\Symfony\Kernel::bootstrap(__DIR__, $loader);