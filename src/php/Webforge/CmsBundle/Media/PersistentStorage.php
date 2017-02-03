<?php

namespace Webforge\CmsBundle\Media;

use Webforge\Common\DateTime\DateTime;

class PersistentStorage {

  /**
   * The name of the entity in the app/project-Model implementing: Model\MediaFileEntityInterface
   * @var string
   */
  private $binaryEntityName;

  private $dc;

  private $treeCache;

  public function __construct($dc, $binaryEntityName, $treeEntityName) {
    $this->dc = $dc;
    $this->binaryEntityName = $binaryEntityName;;
    $this->treeEntityName = $treeEntityName;
    $this->treeRepository = $this->dc->getRepository($this->treeEntityName);
    $this->fileRepository = $this->dc->getRepository($this->binaryEntityName);
  }

  public function persistFile($mediaKey, $path, $name) {
    $binaryClass = $this->dc->expandEntityName($this->binaryEntityName);
    $binary = new $binaryClass();
    $binary->setMediaFileKey($mediaKey);
    $binary->setMediaName($name);

    $this->dc->persist($binary);
    return $binary;
  }

  /**
   * Returns the current mediaStree
   * @return \Webforge\CmsBundle\Media\Tree
   */
  public function loadTree() {
    if (!isset($this->treeCache)) {
      $treeEntity = $this->treeRepository->findOneBy(array(), array('created'=>'DESC'));

      if ($treeEntity) {
        $this->treeCache = unserialize($treeEntity->getContent());
      } else {
        $this->treeCache = Tree::createEmpty();
      }
    }

    return $this->treeCache;
  }

  public function saveTree(Tree $tree) {
    $treeEntityClass = $this->dc->expandEntityName($this->treeEntityName);
    $treeEntity = new $treeEntityClass();
    $treeEntity->setContent(serialize($tree));
    $treeEntity->setCreated(DateTime::now());
    $this->dc->persist($treeEntity);

    return $treeEntity;
  }

  /**
   * Gets a file entity representation be mediaFileKey
   * 
   * @param  string $key the UUID of the file
   * @return Webforge\CmsBundle\Model\MediaFileEntityInterface
   */
  public function loadFile($key) {
    return $this->fileRepository->findOneBy(array('mediaFileKey'=>$key));
  }

  public function loadFiles(Array $keys) {
    return $this->fileRepository->findBy(array('mediaFileKey'=>$keys));
  }

  public function deleteFileByKey($key) {
    $file = $this->loadFile($key);

    if ($file) {
      $this->dc->remove($file);
    }
  }

  public function flush() {
    $this->dc->flush();
  }
}
