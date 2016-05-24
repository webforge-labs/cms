<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Webforge\CmsBundle\Model\GaufretteFileInterface;
use Webforge\Gaufrette\Index as GaufretteIndex;
use Webforge\Gaufrette\File as GaufretteFile;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;

use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GaufretteBinaryHandler {

  private $filesystem, $cacheManager, $dataManager, $imagine;
  protected $thumbnailFilters;

  public function __construct($filesystemMap, $filesystemName, $cacheManager, $dataManager, $filterManager, \Imagine\Image\ImagineInterface $imagine, $filterConfiguration) {
    $this->filesystem = $filesystemMap->get($filesystemName);
    $this->index = new GaufretteIndex($this->filesystem);
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

  public function serialize(JsonSerializationVisitor $visitor, GaufretteFileInterface $binary, array $type, Context $context)  {
    // @TODO i want to export all serialized properties here, what should i use the visitor? the context?
    // $serialized = $visitor->getNavigator()->accept($binary, $type, $context);

    $file = new \stdClass;
    $file->id = $binary->getId();
    $file->key = $binary->getGaufretteKey();

    try {
      $gaufretteFile = $this->index->getFile($binary->getGaufretteKey());

      $this->serializeToFile($gaufretteFile, $file);
      $file->isExisting = TRUE;

    } catch (\Gaufrette\Exception\FileNotFound $e) {
      $file->isExisting = FALSE;
    }

    // we return an array here, because otherwise @inline in serializer will not work
    return (array) $file;
  }

  public function serializeToFile(GaufretteFile $gFile, \stdClass $file) {

    $file->url = '/cms/media?download=1&file='.urlencode($gFile->getRelativePath());
    $file->mimeType = $gFile->mimeType;

    if ($gFile->isImage()) {
      $file->thumbnails = [];
      foreach ($this->thumbnailFilters as $filter) {
        $this->applyFilterFor($gFile, $filter);
        $meta = $this->cache->fetch(\Webforge\CmsBundle\Imagine\MetaWebPathResolver::cacheKey($gFile->key, $filter));

        $meta->url = $this->cacheManager->getBrowserPath($gFile->getRelativePath(), $filter);
        $meta->name = $filter;

        $file->thumbnails[$filter] = $meta;
      }
    }
  }

  protected function applyFilterFor(GaufretteFile $gFile, $filter) {
    $path = $gFile->key;
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
                throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
            }
  }

  public function asTree() {
    $that = $this;
    $options = [
      'withFile'=>function(GaufretteFile $gFile, \stdClass $file) use ($that) {
        return $that->serializeToFile($gFile, $file);
      }
    ];

    return (object) ['root'=>$this->index->asTree($options)];
  }
}
