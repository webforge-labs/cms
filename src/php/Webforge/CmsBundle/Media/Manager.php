<?php

namespace Webforge\CmsBundle\Media;

use URLify;
use Gaufrette\Filesystem;
use Webforge\Gaufrette\Index;
use Ramsey\Uuid\Uuid;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Manager {

  private $serializeHandlers;
  private $treeModified = FALSE;

  private $filesystem;
  private $storage;

  private $router;

  public function __construct(Filesystem $filesystem, Index $gaufretteIndex, PersistentStorage $storage, Array $serializeHandlers, $router) {
    $this->filesystem = $filesystem;
    $this->gaufretteIndex = $gaufretteIndex;
    $this->storage = $storage;
    $this->serializeHandlers = $serializeHandlers;
    $this->router = $router;
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

  protected function indexFile(MediaFileEntityInterface $entity, $path, $name) {
    $tree = $this->storage->loadTree();

    try {
      $tree->addNode($path, $name, $entity->getMediaFileKey());
    } catch (\LogicException $e) {
      $fullPath = $path.$name;
      $alreadyExists = new FileAlreadyExistsException(sprintf('File "%s" does already exist and will not be overriden', $fullPath), 0, $e);
      $alreadyExists->path = $fullPath;
      $alreadyExists->mediaKey = $e->mediaKey;
      throw $alreadyExists;
    }

    $this->treeModified = $tree;
  }

  /**
   * Returns the entity from the path
   * 
   * @param  string $path
   * @return MediaFileEntityInterface
   */
  public function findFileByPath($path) {
    $tree = $this->storage->loadTree();
    $node = $tree->findNode($path);

    if (!$node instanceof FileNode) {
      throw new \LogicException('File wasnt found by path: '.$path);
    }

    $entity = $this->storage->loadFile($node->getMediaKey());

    return $entity;
  }

  public function findFiles(Array $keys) {
    return $this->storage->loadFiles($keys);
  }

  public function deleteFileByKey($key) {
    $this->filesystem->delete($key);
    $this->storage->deleteFileByKey($key);

    $tree = $this->storage->loadTree();
    $tree->removeNodeByKey($key);

    $this->treeModified = $tree;
  }

  public function moveByPath($sourcePath, $targetPath) {
    $tree = $this->storage->loadTree();
    $tree->moveNode($sourcePath, $targetPath);

    $this->treeModified = $tree;
  }

  public function renameByPath($sourcePath, $name) {
    $tree = $this->storage->loadTree();
    $node = $tree->renameNode($sourcePath, $name);

    if ($node instanceof FileNode) {
      $entity = $this->storage->loadFile($node->getMediaKey());
      $entity->setMediaName($name);
    }

    $this->treeModified = $tree;
  }

  public function serializeFile($mediaKey, \stdClass $file) {
    $entity = $this->storage->loadFile($mediaKey);

    if (!$entity) {
      throw new \RuntimeException(sprintf('Entity not found with mediaKey: "%s"', $mediaKey));
    }

    return $this->serializeEntity($entity, $file);
  }

  public function serializeEntity(MediaFileEntityInterface $entity, \stdClass $file) {
    $mediaKey = $entity->getMediaFileKey();

    // those properties will be defined for non existing files
    $file->key = $mediaKey;
    $file->url = $this->router->generate('public_media_original', array('key'=>$mediaKey, 'name'=>$entity->getMediaName()), UrlGeneratorInterface::ABSOLUTE_URL);
    $file->name = $entity->getMediaName();

    try {
      $mimeType = $this->filesystem->mimeType($mediaKey);
    } catch (\Gaufrette\Exception\FileNotFound $e) {
      throw new FileNotFoundException('file with: "'.$mediaKey.'" not found in gaufrette storage', 0, $e);
    } catch (\LogicException $e) {
      $mimeType = NULL;
    }

    $mediaFile = new File($entity->getMediaFileKey(), $entity->getMediaName(), $mimeType);
    $file->mimeType = $mediaFile->getMimeType();
    $file->isExisting = TRUE;

    foreach ($this->serializeHandlers as $handler) {
      $handler->serializeToFile($mediaFile, $entity, $file);
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

    $scalar = $tree->asScalar($options);

    $this->afterSerialization();

    return $scalar;
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

  public function afterSerialization() {
    // flush cached metadata to entities
    $this->storage->flush();
  }

  protected function createMediaKey($path, $name, $contents) {
    return Uuid::uuid4()->toString();
  }

  private function normalizeFilename($name) {
    return URLify::filter($name, 120, 'de', $isFilename = true);
  }
}
