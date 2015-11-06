<?php

$app = new Silex\Application();
$app['debug'] = true;

$webforge = new Webforge\Silex\Injector($app, __DIR__);
$webforge->injectTwig();

