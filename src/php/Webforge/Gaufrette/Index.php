<?php

namespace Webforge\Gaufrette;

class Index {

  public function __construct($filesystem) {
    $this->filesystem = $filesystem;
  }

  public function asTree() {
    return $this->getRoot()->export();
  }

  public function getRoot() {
    $root = new Directory('home', NULL, 'ROOT');
    $directories = array();

    foreach ($this->filesystem->keys() as $key) {

      if (!$this->filesystem->isDirectory($key)) {
        $file = $this->filesystem->get($key);

        $path = explode('/', ltrim($key, '/'));
        $fileName = array_pop($path);

        $parentDirectory = $root;
        $directory = $root;
        $directoryPath = '';
        foreach ($path as $directoryName) {
          $directoryPath = $directoryPath.$directoryName.'/';
          if (!array_key_exists($directoryPath, $directories)) {
            $directories[$directoryPath] = new Directory($directoryName, $parentDirectory);
          }
          $directory = $directories[$directoryPath];

          if (!$parentDirectory->hasItem($directory)) { // this is true for every new Directory, but not for every existing
            $parentDirectory->addItem($directory);
          }
          $parentDirectory = $directory;
        }
        
        $directory->addItem($file = new File($fileName, $directory));
        $file->key = $key;
      }
    }

    return $root;
  }
}
