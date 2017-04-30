<?php

namespace %project.bundle_namespace%\Twig;

use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class GlobalVariablesExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface {

  private $dc;
  private $objectGraph;
  private $site;
  private $frontendDebug;

  public function __construct($dc, $objectGraph, $site, $frontendDebug) {
    $this->dc = $dc;
    $this->objectGraph = $objectGraph;
    $this->site = $site;
    $this->frontendDebug = $frontendDebug;
  }

  public function getGlobals() {
    $globals = [
      'frontendDebug'=>$this->frontendDebug,

      'site'=>$this->site->export(),

      'cms'=>[
        'title'=>'CMS für %project.name%',
        'xsTitle'=>'CMS für %project.name%',
        'site'=>[
          'title'=>'zur Webseite',
          'url'=>'/'
        ]
      ]
    ];

    return $globals;
  }

  public function getName() {
    return '%project.bundle_name_dashed%_global_variables_extension';
  }
}
