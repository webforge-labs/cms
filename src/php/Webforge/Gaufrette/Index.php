<?php

namespace Webforge\Gaufrette;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Index {

  public function __construct($filesystem) {
    $this->filesystem = $filesystem;
  }

  public function asTree(array $options) {
    $options = $this->getTreeOptionsResolver()->resolve($options);

    return $this->getRoot()->export($options);
  }

  public function getRoot() {
    $root = new Directory('home', '/', NULL, 'ROOT');
    $directories = array();

    foreach ($this->filesystem->keys() as $key) {

      if (!$this->filesystem->isDirectory($key)) {
        $gaufretteFile = $this->filesystem->get($key);

        $path = explode('/', ltrim($key, '/'));
        $fileName = array_pop($path);

        $parentDirectory = $root;
        $directory = $root;
        $directoryPath = '';
        foreach ($path as $directoryName) {
          $directoryPath = $directoryPath.$directoryName.'/';
          if (!array_key_exists($directoryPath, $directories)) {
            $directories[$directoryPath] = new Directory($directoryName, $directoryPath, $parentDirectory);
          }
          $directory = $directories[$directoryPath];

          if (!$parentDirectory->hasItem($directory)) { // this is true for every new Directory, but not for every existing
            $parentDirectory->addItem($directory);
          }
          $parentDirectory = $directory;
        }
        
        $directory->addItem($file = new File($fileName, $directory));
        try {
          $file->mimeType = $this->filesystem->mimeType($key);
        } catch (\LogicException $e) {
          $file->mimeType = NULL;
        }
        $file->key = $key;
        $file->isExisting = TRUE;
      }
    }

    return $root;
  }

  /**
   * Retrieves a single file (with all its directories structured)
   * @param  string $key the gaufretteKey
   * @return Webforge\Gaufrette\File
   */
  public function getFile($key) {
    $root = new Directory('home', '/', NULL, 'ROOT');
    $directories = array();

    $gaufretteFile = $this->filesystem->get($key);

    $path = explode('/', ltrim($key, '/'));
    $fileName = array_pop($path);

    $parentDirectory = $root;
    $directory = $root;
    $directoryPath = '';
    foreach ($path as $directoryName) {
      $directoryPath = $directoryPath.$directoryName.'/';
      if (!array_key_exists($directoryPath, $directories)) {
        $directories[$directoryPath] = new Directory($directoryName, $directoryPath, $parentDirectory);
      }
      $directory = $directories[$directoryPath];

      if (!$parentDirectory->hasItem($directory)) { // this is true for every new Directory, but not for every existing
        $parentDirectory->addItem($directory);
      }
      $parentDirectory = $directory;
    }
    
    $directory->addItem($file = new File($fileName, $directory));
    try {
      $file->mimeType = $this->filesystem->mimeType($key);
    } catch (\LogicException $e) {
      $file->mimeType = NULL;
    }
    $file->key = $key;

    return $file;
  }

  protected function getTreeOptionsResolver() {
    $resolver = new OptionsResolver();
    $resolver->setDefaults(array(
      'withFile' => function(File $gFile, \stdClass $file) {
      },
      'withDirectory' => function(Directory $gDirectory, \stdClass $directory) {
      }
    ));

    $resolver->setAllowedTypes('withFile', 'Closure');
    $resolver->setAllowedTypes('withDirectory', 'Closure');

    return $resolver;
  }
}
