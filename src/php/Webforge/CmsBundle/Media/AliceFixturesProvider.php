<?php

namespace Webforge\CmsBundle\Media;

class AliceFixturesProvider {

  public function __construct($manager) {
    $this->manager = $manager;
  }

  /**
   * Stores a new file in db and gaufrette filesystem
   * 
   * @param string $path     a path with forwardslashes starting from root (think of it as directory)
   * @param string $name     the filename of the file in format given by the user (think of it as filename in the directory in $path)
   * @param string $filePath the location of the contents of the file, relative to project root with forward slashes
   * @return The Entity handling binary
   */
  public function webforgeMediaFile($path, $name, $filePath) {
    $this->manager->beginTransaction();

    $file = $this->manager->addFile($path, $name, $GLOBALS['env']['root']->getFile($filePath)->getContents());

    $this->manager->commitTransaction(); // this will flush doctrine, but i dont know if this breaks things
    return $file;
  }

  public function getWebforgeMediaFile($path) {
    return $this->manager->findFileByPath($path);
  }
}
