<?php

namespace %project.bundle_namespace%\Model;

use %project.bundle_namespace%\Entity\Post;

class ObjectGraph extends \Webforge\Symfony\ObjectGraph {

  private $router;
  private $markdowner;

  public function __construct($serializer, $router, $markdowner) {
    $this->router = $router;
    $this->markdowner = $markdowner;
    parent::__construct($serializer);
  }

  public function serializePosts($posts, Array $groups = NULL) {
    $export = array();

    foreach ($posts as $post) {
      $export[] = $this->serializePost($post, $groups);
    }

    return $export;
  }

  public function serializePost(Post $post, Array $groups = NULL) {
    $exportedPost = $this->serialize($post, $groups);

    if (in_array('post-details', $groups)) {
      // fix that jms serializer is too stupid to serialize objects from stdClass
      $exportedPost->contents = $post->getContents();
    }

    $exportedPost->url = $this->router->generate('post', array('postSlug'=>$post->getSlug()));

    return $exportedPost;
  }
}
