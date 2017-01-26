<?php

namespace Webforge\CmsBundle\Media;

use URLify;
use Gaufrette\Filesystem;
use Webforge\Gaufrette\Index;
use Ramsey\Uuid\Uuid;

class Manager {

  private $serializeHandlers;
  private $treeModified = FALSE;

  private $filesystem;
  private $storage;

  public function __construct(Filesystem $filesystem, Index $gaufretteIndex, PersistentStorage $storage, Array $serializeHandlers) {
    $this->filesystem = $filesystem;
    $this->gaufretteIndex = $gaufretteIndex;
    $this->storage = $storage;
    $this->serializeHandlers = $serializeHandlers;
  }

  /**
   * Stores a file in db and gaufrette filesystem
   * @param string $path     a path with forwardslashes starting from root (think of it as directory)
   * @param string $name     the filename of the file in format given by the user (think of it as filename in the directory in $path)
   * @param string $contents the binary contents of the file
   * @return The Entity handling binaries
   */
  public function addFile($path, $name, $contents) {
    $path = trim($path, '/').'/'; // store without leadingslash but with trailingslash
    $name = $this->normalizeFilename($name);

    $mediaKey = $this->createMediaKey($path, $name, $contents);

    // write data to physical storage
    $this->filesystem->write($mediaKey, $contents);

    // write entity to single table
    $entity = $this->storage->persistFile($mediaKey, $path, $name);

    // index entity in tree
    $this->indexFile($entity, $path, $name);

    return $entity;
  }

  protected function indexFile(\Webforge\CmsBundle\Model\MediaFileEntityInterface $entity, $path, $name) {
    $tree = $this->storage->loadTree();

    try {
      $tree->addNode($path, $name, $entity->getMediaFileKey());
    } catch (\LogicException $e) {
      $fullPath = $path.$name;
      $e = new FileAlreadyExistsException(sprintf('File "%s" does already exist and will not be overriden', $fullPath), 0, $e);
      $e->path = $fullPath;
      throw $e;
    }

    $this->treeModified = $tree;
  }

  public function deleteFileByKey($key) {
    $this->filesystem->delete($key);
    $this->storage->deleteFileByKey($key);

    $tree = $this->storage->loadTree();
    $tree->removeNodeByKey($key);
  }

  public function moveByPath($sourcePath, $targetPath) {
    $tree = $this->storage->loadTree();
    $tree->moveNode($sourcePath, $targetPath);
  }

  public function serializeFile($mediaKey, \stdClass $file) {
    $mediaEntity = $this->storage->loadFile($mediaKey);
    $gaufretteFile = $this->filesystem->get($mediaKey);

    try {
      $mimeType = $this->filesystem->mimeType($mediaKey);
    } catch (\LogicException $e) {
      $mimeType = NULL;
    }

    $mediaFile = new File($mediaEntity->getMediaFileKey(), $mediaEntity->getMediaName(), $mimeType);

    $file->url = '/cms/media?download=1&file='.urlencode($mediaFile->getKey());
    $file->mimeType = $mediaFile->getMimeType();
    $file->key = $mediaFile->getKey();

    foreach ($this->serializeHandlers as $handler) {
      $handler->serializeToFile($mediaFile, $file);
    }
  }

  public function asTree() {
    $that = $this;
    $options = [
      'withFile'=>function(FileNode $node, \stdClass $export) use ($that) {
        return $that->serializeFile($node->getMediaKey(), $export);
      }
    ];

    $tree = $this->storage->loadTree();

    return $tree->asScalar($options);
  }

  public function beginTransaction() {
  }

  public function commitTransaction() {
    if ($this->treeModified) {
      $this->storage->saveTree($this->treeModified);
      $this->treeModified = FALSE;
    }

    $this->storage->flush();
  }

  protected function createMediaKey($path, $name, $contents) {
    return Uuid::uuid4()->toString();
  }

  private function normalizeFilename($name) {
    return URLify::filter($name, 120, 'de', $isFilename = true);
  }
}
