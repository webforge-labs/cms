<?php

namespace Webforge\CmsBundle\Media;

use URLify;
use Gaufrette\Filesystem;
use Webforge\Gaufrette\Index;

class Manager {

  private $serializeHandlers;
  
  public function __construct(Filesystem $filesystem, Index $gaufretteIndex, PersistentStorage $storage, Array $serializeHandlers) {
    $this->filesystem = $filesystem;
    $this->gaufretteIndex = $gaufretteIndex;
    $this->storage = $storage;
    $this->serializeHandlers = $serializeHandlers;
  }

  public function addFile($path, $name, $contents) {
    $path = trim($path, '/').'/'; // store without leadingslash
    $normalizedName = $this->normalizeFilename($name);
    $filePath = $path.$normalizedName;
    try {
      $this->filesystem->write($filePath, $contents);

      $this->storage->persistFile($filePath, $name);

    } catch (\Gaufrette\Exception\FileAlreadyExists $e) {
      $e = new FileAlreadyExistsException(sprintf('File "%s" does already exist and will not be overriden', $filePath), 0, $e);
      $e->path = $filePath;
      throw $e;
    }

    return $filePath;
  }

  public function getFile($key) {
    return $this->gaufretteIndex->getFile($key);
  }

  public function deleteFileByKey($key) {
    $this->filesystem->delete($key);
    $this->storage->deleteFileByGaufretteKey($key);
  }

  public function moveByKey($sourceKey, $targetKey) {
    
  }

  private function normalizeFilename($name) {
    return URLify::filter($name, 120, 'de', $isFilanme = true);
  }

  public function beginTransaction() {

  }

  public function commitTransaction() {
    $this->storage->flush();
  }

  public function serializeFile(MediaFileInterface $mediaFile, \stdClass $file) {
    $file->url = '/cms/media?download=1&file='.urlencode($mediaFile->getFilesystemIdentifier());
    $file->mimeType = $mediaFile->getMimeType();

    foreach ($this->serializeHandlers as $handler) {
      $handler->serializeToFile($mediaFile, $file);
    }
  }

  public function asTree() {
    $that = $this;
    $options = [
      'withFile'=>function(MediaFileInterface $mediaFile, \stdClass $file) use ($that) {
        return $that->serializeFile($mediaFile, $file);
      }
    ];

    return (object) ['root'=>$this->gaufretteIndex->asTree($options)];
  }
}
