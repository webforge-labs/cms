<?php

namespace Webforge\CmsBundle\Media;

use Gaufrette\Filesystem;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use URLify;
use Webforge\CmsBundle\Model\MediaFileEntityInterface;
use Webforge\Gaufrette\Index;

class Manager
{
    const EVENT_FILE_WARMUP = 'webforge.media.file-warmup';

    private $serializeHandlers;
    private $treeModified = false;

    private $filesystem;
    private $storage;

    private $router;

    private $streamWrapperProtocol;
    private $streamWrapperDomain;

    public function __construct(
        Filesystem $filesystem,
        Index $gaufretteIndex,
        PersistentStorage $storage,
        array $serializeHandlers,
        $router,
        $streamWrapperProtocol,
        $streamWrapperDomain
    ) {
        $this->filesystem = $filesystem;
        $this->gaufretteIndex = $gaufretteIndex;
        $this->storage = $storage;
        $this->serializeHandlers = $serializeHandlers;
        $this->router = $router;
        $this->streamWrapperProtocol = $streamWrapperProtocol;
        $this->streamWrapperDomain = $streamWrapperDomain;
    }

    /**
     * Stores a file in db and gaufrette filesystem
     * @param string $path a path with forwardslashes starting from root (think of it as directory)
     * @param string $name the filename of the file in format given by the user (think of it as filename in the directory in $path)
     * @param string $contents the binary contents of the file
     * @return The Entity handling binaries
     * @throws FileAlreadyExistsException
     */
    public function addFile($path, $name, $contents)
    {
        $path = $this->normalizePath($path);
        $name = $this->normalizeFilename($name);

        $mediaKey = $this->createMediaKey($path, $name, $contents);

        // write data to physical storage
        $this->savePhysicalFile($contents, $mediaKey);

        // write entity to single table
        $entity = $this->storage->persistFile($mediaKey, $path, $name);

        // index entity in tree
        $this->indexFile($entity, $path, $name);

        return $entity;
    }

    /**
     * Stores a file in db and gaufrette filesystem
     * @param string $path a path with forwardslashes starting from root (think of it as directory)
     * @param string $name the filename of the file in format given by the user (think of it as filename in the directory in $path)
     * @param string $contents the binary contents of the file
     * @return The Entity handling binaries
     */
    public function addOrUpdateFile($path, $name, $contents, &$wasUpdated)
    {
        try {
            $entity = $this->findFileByPath($this->getNormalizedPath($path, $name));
            if ($entity === NULL) {
                throw new \Exception('The entity was found by path: '.$path.'//'.$name.' (e.g. is in media tree), but not existing anymore in entities (thats strange)');
            }

            $wasUpdated = true;

            // we need to use a new media key, because the file content has changed and so it should not be cached inoorrectly
            // however we can keep the entity and its saved relationships
            // this is problematic for mechanisms that work with the key!
            $oldKey = $entity->getMediaFileKey();

            $path = $this->normalizePath($path);
            $name = $this->normalizeFilename($name);

            $entity->setMediaFileKey($newKey = $this->createMediaKey($path, $name, $contents));

            $this->savePhysicalFile($contents, $newKey);

            $this->updateIndexFile($entity, $path, $name, $oldKey);

            // delete the old file only, if it's not used in another binary (@TODO we need that, if we change the hashing to sha1
            $this->filesystem->delete($oldKey);

            return $entity;
        } catch (\LogicException $e) {
            $wasUpdated = false;

            return $this->addFile($path, $name, $contents);
        }
    }

    protected function indexFile(MediaFileEntityInterface $entity, $path, $name)
    {
        $tree = $this->storage->loadTree();

        try {
            $tree->addNode($path, $name, $entity->getMediaFileKey());
        } catch (\LogicException $e) {
            $fullPath = $path.$name;
            $alreadyExists = new FileAlreadyExistsException(
                sprintf('File "%s" does already exist and will not be overriden', $fullPath),
                0,
                $e
            );
            $alreadyExists->path = $fullPath;
            $alreadyExists->mediaKey = $e->mediaKey;
            throw $alreadyExists;
        }

        $this->treeModified = $tree;
    }

    protected function updateIndexFile(MediaFileEntityInterface $entity, $path, $name, $oldKey)
    {
        $tree = $this->storage->loadTree();

        $tree->removeNodeByKey($oldKey);
        $tree->addNode($path, $name, $entity->getMediaFileKey());

        $this->treeModified = $tree;
    }

    /**
     * Returns the entity from the path
     *
     * @param  string $path
     * @return MediaFileEntityInterface
     */
    public function findFileByPath($path)
    {
        $tree = $this->storage->loadTree();
        $node = $tree->findNode($path);

        if (!$node instanceof FileNode) {
            throw new \LogicException('File wasnt found by path: '.$path);
        }

        $entity = $this->storage->loadFile($node->getMediaKey());

        return $entity;
    }

    public function getNormalizedPath($folder, $name)
    {
        return $this->normalizePath($folder).$this->normalizeFilename($name);
    }

    /**
     * Returns a StreamWrapper-URL for the given entity
     *
     * @param  MediaFileEntityInterface $entity
     * @return string
     */
    public function getStreamUrl(MediaFileEntityInterface $entity)
    {
        return sprintf(
            '%s://%s/%s',
            $this->streamWrapperProtocol,
            $this->streamWrapperDomain,
            $entity->getMediaFileKey()
        );
    }

    /**
     * @return Webforge\CmsBundle\Model\MediaFileEntityInterface[]
     */
    public function findFiles(array $keys)
    {
        return $this->storage->loadFiles($keys);
    }

    public function deleteFileByKey($key)
    {
        $this->filesystem->delete($key);
        $this->storage->deleteFileByKey($key);

        $tree = $this->storage->loadTree();
        $tree->removeNodeByKey($key);

        $this->treeModified = $tree;
    }

    public function moveByPath($sourcePath, $targetPath)
    {
        $tree = $this->storage->loadTree();
        $tree->moveNode($sourcePath, $targetPath);

        $this->treeModified = $tree;
    }

    public function renameByPath($sourcePath, $name)
    {
        $tree = $this->storage->loadTree();
        $node = $tree->renameNode($sourcePath, $name);

        if ($node instanceof FileNode) {
            $entity = $this->storage->loadFile($node->getMediaKey());
            $entity->setMediaName($name);
        }

        $this->treeModified = $tree;
    }

    public function serializeFile($mediaKey, \stdClass $file, array $options = array())
    {
        $entity = $this->storage->loadFile($mediaKey);

        if (!$entity) {
            throw new MediaEntityNotFoundException(sprintf('Entity not found with mediaKey: "%s"', $mediaKey));
        }

        return $this->serializeEntity($entity, $file, $options);
    }

    public function serializeEntity(MediaFileEntityInterface $entity, \stdClass $file, array $options = array())
    {
        $mediaKey = $entity->getMediaFileKey();

        // those properties will be defined for non existing files
        $file->key = $mediaKey;
        $file->url = $this->router->generate(
            'public_media_original',
            array('key' => $mediaKey, 'name' => $entity->getMediaName()),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $file->name = $entity->getMediaName();

        try {
            $mimeType = $this->filesystem->mimeType($mediaKey);
        } catch (\Gaufrette\Exception\FileNotFound $e) {
            throw new FileNotFoundException('file with: "'.$mediaKey.'" not found in gaufrette storage', 0, $e);
        } catch (\LogicException $e) {
            $mimeType = null;
        }

        $mediaFile = new File($entity->getMediaFileKey(), $entity->getMediaName(), $mimeType);
        $file->mimeType = $mediaFile->getMimeType();
        $file->isExisting = true;

        foreach ($this->serializeHandlers as $handler) {
            $handler->serializeToFile($mediaFile, $entity, $file, $options);
        }
    }

    public function asTree(array $options = array())
    {
        $that = $this;

        $treeOptions = [
            'withFile' => function (FileNode $node, \stdClass $export) use ($that, $options) {
                try {
                    $that->serializeFile($node->getMediaKey(), $export, $options);
                    return true;
                } catch (FileNotFoundException $e) {
                    return false;
                }
            }
        ];

        $tree = $this->storage->loadTree();

        $scalar = $tree->asScalar($treeOptions);

        $this->afterSerialization();

        return $scalar;
    }

    public function beginTransaction()
    {
    }

    public function commitTransaction()
    {
        if ($this->treeModified) {
            $this->storage->saveTree($this->treeModified);
            $this->treeModified = false;
        }

        $this->storage->flush();
    }

    public function afterSerialization()
    {
        // flush cached metadata to entities
        $this->storage->flush();
    }

    protected function createMediaKey($path, $name, $contents)
    {
        //return sha1($contents); this works okayish, but we cannot delete physical files then and more then
        //one entity might have the same physical files, i have to think about that first
        return Uuid::uuid4()->toString();
    }

    private function normalizeFilename($name)
    {
        return URLify::filter($name, 120, 'de', $isFilename = true);
    }

    /**
     * @param $path
     * @return string
     */
    protected function normalizePath($path): string
    {
        return trim($path, '/').'/';  // store without leadingslash but with trailingslash
    }

    /**
     * @param $contents
     * @param $key
     */
    protected function savePhysicalFile($contents, $key): void
    {
        try {
            $this->filesystem->write($key, $contents);
        } catch (\Gaufrette\Exception\FileAlreadyExists $e) {
            // we hope our hashing has found a real-duplicate image here, so we connect 2 binaries with one physical key file
        }
    }
}
