<?php

namespace Webforge\CmsBundle\Serialization;

use Webforge\CmsBundle\Model\MediaFileInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use RuntimeException;

class ImagineThumbnailsFileHandler implements MediaFileHandlerInterface {

  protected $thumbnailFilters;
  protected $cacheManager;
  protected $dataManager;
  protected $imagine;

  public function __construct(CacheManager $cacheManager, DataManager $dataManager, FilterManager $filterManager, ImagineInterface $imagine, FilterConfiguration $filterConfiguration) {
    $this->cacheManager = $cacheManager;
    $this->filterManager = $filterManager;
    $this->dataManager = $dataManager;
    $this->imagine = $imagine;
    $this->cache = new \Doctrine\Common\Cache\ChainCache([
      new \Doctrine\Common\Cache\ArrayCache(),
      new \Doctrine\Common\Cache\FilesystemCache($GLOBALS['env']['root']->sub('files/cache/imagine-meta')->wtsPath())
    ]);

    $this->thumbnailFilters = array();
    foreach ($filterConfiguration->all() as $key=>$filter) {
      if (isset($filter['filters']['thumbnail'])) {
        $this->thumbnailFilters[] = $key;
      }
    }
  }

  public function serializeToFile(MediaFileInterface $mediaFile, \stdClass $file) {
    if ($mediaFile->isImage()) {
      $file->thumbnails = [];
      foreach ($this->thumbnailFilters as $filter) {
        $this->applyFilterFor($mediaFile, $filter);
        $meta = $this->cache->fetch(\Webforge\CmsBundle\Imagine\MetaWebPathResolver::cacheKey($mediaFile->getKey(), $filter));

        if (!$meta) {
          throw new NotLoadableException('No meta cache for file with gaufretteKey: '.$mediaFile->getKey());
        }
        $meta->url = $this->cacheManager->getBrowserPath($mediaFile->getKey(), $filter);
        $meta->name = $filter;

        $file->thumbnails[$filter] = $meta;
      }
    }
  }

  protected function applyFilterFor(MediaFileInterface $mediaFile, $filter) {
    $path = $mediaFile->getKey();
            try
            {
                if(!$this->cacheManager->isStored($path, $filter))
                {
                    try
                    {
                        $binary = $this->dataManager->find($filter, $path);
                    }
                    catch(NotLoadableException $e)
                    {
                        if($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter))
                        {
                            return $defaultImageUrl;
                        }

                        throw new NotFoundHttpException('Source image could not be found', $e);
                    }

                    $this->cacheManager->store(
                        $this->filterManager->applyFilter($binary, $filter),
                        $path,
                        $filter
                    );
                }

            }
            catch(RuntimeException $e)
            {
                throw new RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
            }
  }
}
