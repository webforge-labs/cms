<?php

use Webforge\Common\System\Dir;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$GLOBALS['env']['root'] = Dir::factoryTS(__DIR__);

require __DIR__.'/app/AppKernel.php';
//require_once __DIR__.'/app/AppCache.php';

return $loader;