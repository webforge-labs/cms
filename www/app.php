<?php


require_once __DIR__.'/../bootstrap.php';

$app = new Silex\Application();
$app['debug'] = true;

$webforge = new Webforge\Silex\Injector($app, __DIR__);
$webforge->injectTwig();


$app->get('/', function () use ($app) {
  return $app['twig']->render('base.html.twig', array(
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
            'tab'=>array(
              'label'=>'Seiten verwalten',
              'id'=>'pages-list',
              'url'=>'/cms/pages/list'
            )
          )
        ),
        'Navigation'=>array(
          array(
            'label'=>'Hauptnavigation pflegen',
            'tab'=>array(
              'label'=>'Hauptnavigation',
              'id'=>'main-navigation',
              'url'=>'/cms/navigation/main'
            )
          )
        )
      )
    )
  ));
});

$app->get('/cms/dashboard', function() use ($app) {
  return $app['twig']->render('test/dashboard.html.twig', array(
    'user'=>array(
      'name'=>'Imme'
    ),

    'tapir'=>'tapire'
  ));
});

$app->get('/cms/users/list', function() use ($app) {
  return $app['twig']->render('test/users/list.html.twig', array(

  ));
});

$app->run();