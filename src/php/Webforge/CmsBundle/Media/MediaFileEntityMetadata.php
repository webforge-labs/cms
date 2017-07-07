<?php

namespace Webforge\CmsBundle\Media;

use stdClass;

trait MediaFileEntityMetadata {

  public function setMediaMetadata($key, stdClass $meta) {
    if (!$this->mediaMeta instanceof stdClass) {
      $this->mediaMeta = new stdClass;
    }

    $this->mediaMeta->{$key} = $meta;
  }

  public function getMediaMetadata($key) {
    if (isset($this->mediaMeta) && property_exists($this->mediaMeta, $key)) {
      return $this->mediaMeta->{$key};
    }

    return NULL;
  }
}
