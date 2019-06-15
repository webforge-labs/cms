<?php

namespace Webforge\CmsBundle\Model;

/**
 * Entities implementing that interface will be mapped to a special serializer type that converts images and serializes infos for the media file
 */
interface MediaFileEntityInterface
{
    public function getMediaFileKey();

    public function setMediaFileKey($key);

    public function getMediaName();

    /**
     * Sets a descriptive name - used as a filename in outputs
     * @param string $filename
     */
    public function setMediaName($filename);


    /**
     * Adds arbitrary metadata to the binary to be "cached"
     *
     * @param string $key the key where to getMediaMeta() later
     * @param \stdClass $meta your meta data
     */
    public function setMediaMetadata($key, \stdClass $meta);

    public function resetMediaMetadata();

    /**
     * Returns media meta stored under specific key
     * @param  string $key the key under which you have stored media data with setMediaMeta
     * @return \stdClass or NULL   if it wasnt set
     */
    public function getMediaMetadata($key);
}
