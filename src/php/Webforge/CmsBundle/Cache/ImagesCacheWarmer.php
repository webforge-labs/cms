<?php

namespace Webforge\CmsBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class ImagesCacheWarmer implements CacheWarmerInterface
{
    protected $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function warmUp($cacheDir)
    {
        $this->manager->asTree();
    }

    public function isOptional()
    {
        return true;
    }
}
