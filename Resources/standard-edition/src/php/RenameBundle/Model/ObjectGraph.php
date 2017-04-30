<?php

namespace %project.bundle_namespace%\Model;

class ObjectGraph extends \Webforge\Symfony\ObjectGraph {

  private $router;
  private $markdowner;

  public function __construct($serializer, $router, $markdowner) {
    $this->router = $router;
    $this->markdowner = $markdowner;
    parent::__construct($serializer);
  }

  public function serializeSomething($entity, Array $groups = NULL) {
    $exported = $this->serialize($entity, $groups);

    $exported->html = $this->markdowner->transformMarkdown($exported->markdown);

    return $exported;
  }
}
