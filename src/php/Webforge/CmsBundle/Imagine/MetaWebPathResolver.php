<?php

namespace Webforge\CmsBundle\Imagine;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

class MetaWebPathResolver implements ResolverInterface {

  protected $imagine;
  protected $resolver;

  public function __construct(WebPathResolver $resolver) {
    $this->resolver = $resolver;
    $this->cache = new \Doctrine\Common\Cache\ChainCache([
      new \Doctrine\Common\Cache\ArrayCache(),
      new \Doctrine\Common\Cache\FilesystemCache($GLOBALS['env']['root']->sub('files/cache/imagine-meta')->wtsPath())
    ]);
  }

  public function setImagine(\Imagine\Image\ImagineInterface $imagine) {
    $this->imagine = $imagine;
  }

  public function resolve($path, $filter) {
    return $this->resolver->resolve($path, $filter);
  }

  public function isStored($path, $filter) {
    return $this->resolver->isStored($path, $filter);
  }

  public function remove(array $paths, array $filters) {
    if (!empty($paths)) {
      // what if empty paths? how to delete all cache keys with any path?
      foreach ($paths as $path) {
        foreach ($filters as $filter) {
          $this->cache->delete(self::cacheKey($path, $filter));
        }
      }
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function store(BinaryInterface $binary, $path, $filter) {
    if ($binary instanceof FileBinaryInterface) {
      $image = $this->imagine->open($binary->getPath());
    } else {
      $image = $this->imagine->load($binary->getContent());
    }

    $size = $image->getSize();

    $meta = (object) [
      'isPortrait'=>$isPortrait = ($size->getHeight() > $size->getWidth()),
      'isLandscape'=>$size->getWidth() > $size->getHeight(),
      'orientation'=>$isPortrait ? 'portrait' : 'landscape', // square === landscape
    ];

    // this path is without leading slash
    $key = self::cacheKey($path, $filter);

    $this->cache->save($key, $meta);

    return $this->resolver->store($binary, $path, $filter);
  }

  public static function cacheKey($path, $filter) {
    return $filter.'::'.$path;
  }
}
