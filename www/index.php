<?php


require_once __DIR__.'/../bootstrap.php';

$app = new Silex\Application();
$app['debug'] = true;

$webforge = new Webforge\Silex\Injector($app, __DIR__);
$webforge->injectTwig();


$app->get('/', function () use ($app) {
  return $app['twig']->render('base.html.twig', array(
    'tapir'=>'tapir-front-1',
    'tapir'=>'tapire',

    'user'=>array(
      'name'=>'Imme'
    ),

    'data'=>'{}',

    'sidebar'=>array(
      'groups'=>array(
        'CMS'=>array(
          array(
            'label'=>'Benutzer verwalten',
            'tab'=>array(
              'label'=>'Benutzer verwalten',
              'id'=>'users-list',
              'url'=>'/cms/users/list'
            )
          )
        ),
        'Webseite'=>array(
          array(
            'label'=>'Seiten verwalten',
            'tab'=>array()
          )
        ),
        'Navigation'=>array(
          array(
            'label'=>'Hauptnavigation pflegen',
            'tab'=>array()
          )
        )
      )
    )
  ));
});

$app->run();