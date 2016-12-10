<?php

namespace Webforge\CmsBundle\Media;

class PersistentStorage {

  /**
   * The name of the entity in the app/project-Model implementing: Model\MediaFileInterface
   * @var string
   */
  private $binaryEntityName;

  private $dc;

  public function __construct($dc, $binaryEntityName) {
    $this->dc = $dc;
    $this->binaryEntityName = $binaryEntityName;;
  }

  public function persistFile($gaufretteKey, $originalName) {
    $binaryClass = $this->dc->expandEntityName($this->binaryEntityName);
    $binary = new $binaryClass();
    $binary->setGaufretteKey($gaufretteKey);
    $binary->setOriginalName($originalName);

    $this->dc->persist($binary);
    return $binary;
  }

  public function deleteFileByGaufretteKey($key) {
    $file = $this->findFileByGaufretteKey($key);

    if ($file) {
      $this->dc->remove($file);
    }
  }

  private function findFileByGaufretteKey($key) {
    return $this->dc->getRepository($this->binaryEntityName)->findOneBy(array('gaufretteKey'=>$key));
  }

  public function flush() {
    $this->dc->flush();
  }
}
