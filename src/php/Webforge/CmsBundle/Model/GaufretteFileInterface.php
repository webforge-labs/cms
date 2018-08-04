<?php

namespace Webforge\CmsBundle\Model;

/**
 * Entities implementing that interface will be mapped to a special serializer type that converts images and serializes infos for the media file
 */
interface GaufretteFileInterface
{
    public function getGaufretteKey();

    public function setGaufretteKey($key);
}
