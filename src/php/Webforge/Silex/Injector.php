<?php

namespace Webforge\Silex;

class Injector {

  public function __construct($app, $appRoot) {
    $this->app = $app;
    $this->root = rtrim($appRoot, '\\/');
  }

  public function injectTwig() {
    $app->register(new Silex\Provider\TwigServiceProvider(), array(
      'twig.path' => $this->root.'/Resources/tpl',
    ));
  }

  /**
   * Mustache provider
   * 
   * composer require "mustache/silex-provider":"~1.0"
   */
  public function injectMustache() {
    $this->app->register(new \Mustache\Silex\Provider\MustacheServiceProvider, array(
      'mustache.path' => $this->root.'/../Resources/tpl',
      'mustache.options' => array(
        'cache' => $this->root.'/../files/cache/mustache',
      )
    ));
  }
}
