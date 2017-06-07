<?php

namespace %project.bundle_namespace%;

use Webforge\Common\DateTime\DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Site {

  private $dc;
  private $now;
  private $router;

  public function __construct($dc, DateTime $now, $router) {
    $this->dc = $dc;
    $this->now = $now;
    $this->router = $router;
  }

  public function getAuthor(){
    return 'Philipp Scheit';
  }

  public function getName() {
    return '%project.name%';
  }

  public function getPageTitle($add) {
    return $this->getName().' - '.$add;
  }

  public function getFeedUrl(){
    // @FIXME
    return 'http://feeds.feedburner.com/%project.nicename%';

    return $this->router->generate('feed-de', array(), UrlGeneratorInterface::ABSOLUTE_URL);
  }

  public function getDefaultDescription() {
    // @FIXME
    return 'The desc for my page';
  }


  public function export() {
    return [
      'defaultDescription'=>$this->getDefaultDescription(),
      'feedUrl'=>$this->getFeedUrl(),
      'name'=>$this->getName(),
      'title'=>$this->getName(),
      'author'=>$this->getAuthor(),
      // @FIXME
      //'twitterHandle'=>'@something',
      'googleAnalytics' => [
        'enabled'=>false,
        'id'=>NULL
      ]
    ];
  }
}
