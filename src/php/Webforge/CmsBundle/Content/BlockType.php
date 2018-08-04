<?php

namespace Webforge\CmsBundle\Content;

class BlockType
{
    private $properties;

    public function __construct($name, array $properties)
    {
        $this->properties = $properties;
    }

    public function getProperties()
    {
        return $this->properties;
    }
}
