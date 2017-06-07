<?php

use Carbon\Carbon;

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__.'/vendor/autoload.php';

Carbon::setLocale('de');

return Webforge\Symfony\Kernel::bootstrap(__DIR__, $loader);