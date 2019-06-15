<?php

namespace Webforge\CmsBundle\Media;

use stdClass;

trait MediaFileEntityMetadata
{


    public function setMediaMetadata($key, stdClass $meta)
    {
        // create a clone of the existing to make doctrine detect the change
        $mediaMeta = ($this->mediaMeta instanceof \stdClass) ? clone $this->mediaMeta : new \stdClass;
        $mediaMeta->{$key} = $meta;

        $this->mediaMeta = $mediaMeta;
    }

    public function getMediaMetadata($key)
    {
        if (isset($this->mediaMeta) && property_exists($this->mediaMeta, $key)) {
            return $this->mediaMeta->{$key};
        }

        return null;
    }

    public function resetMediaMetadata()
    {
        $this->mediaMeta = NULL;
    }
}
