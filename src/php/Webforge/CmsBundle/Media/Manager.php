<?php

namespace Webforge\CmsBundle\Media;

use URLify;
use Gaufrette\Filesystem;
use Webforge\Gaufrette\Index;

class Manager {
  
  public function __construct(Filesystem $filesystem, Index $gaufretteIndex, PersistentStorage $storage) {
    $this->filesystem = $filesystem;
    $this->gaufretteIndex = $gaufretteIndex;
    $this->storage = $storage;
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

  public function asTree(Array $options) {
    return $this->gaufretteIndex->asTree($options);
  }

  private function normalizeFilename($name) {
    return URLify::filter($name, 120, 'de', $isFilanme = true);
  }

  public function beginTransaction() {

  }

  public function commitTransaction() {
    $this->storage->flush();
  }
}
