<?php

namespace Webforge\Gaufrette;

use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;
use Webforge\Testing\ObjectAsserter;

class GaufretteIndexTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    parent::setUp();
    $adapter = new InMemory(
      array(
        '2016-04-13/file1.jpg' => 'content from file1.jpg',
        '2016-04-13/file2.jpg' => 'content',
        '2016-04-13/set2/trees.png' => 'content from trees',
        'backup/2016-04-13/set2/trees.png' => 'backuped content from trees',
        'root.txt' => 'readme'
      )
    );

    $filesystem = new Filesystem($adapter);
    $this->index = new Index($filesystem);
  }

  public function testGettingAsTree() {
    $options = [
      'withFile'=>function(File $gFile, \stdClass $file) {
        $file->myCustomPath = $gFile->getRelativePath();
      }
    ];

    $object = new ObjectAsserter($this->index->asTree($options), $this);

    $object->property('name')->end()
      ->property('type', 'ROOT')->end()
      ->property('items')->isArray()->length(3)
        ->key(0)
          ->property('name', '2016-04-13')->end()
          ->property('items')->isArray()->length(3)
            ->key(0)
              ->property('name', 'file1.jpg')->end()
              ->property('myCustomPath', '/2016-04-13/file1.jpg')->end()
            ->end()
            ->key(1)
              ->property('name', 'file2.jpg')->end()
              ->property('isExisting', true)->end()
            ->end()
            ->key(2)
              ->property('name', 'set2')->end()
              ->property('type', 'directory')->end()
              ->property('items')->isArray()->length(1)
                ->key(0)
                  ->property('name', 'trees.png')->end()
                  ->property('myCustomPath', '/2016-04-13/set2/trees.png')->end()
                  ->property('isExisting', true)->end()
                ->end()
              ->end()
            ->end()
          ->end()
        ->end()

        ->key(1)
          ->property('name', 'backup')->end()
          ->property('items')->isArray()->length(1)
            ->key(0)
              ->property('name', '2016-04-13')->end()
              ->property('items')->isArray()->length(1)
                ->key(0)
                  ->property('name', 'set2')->end()
                  ->property('items')->isArray()->length(1)
                    ->key(0)
                      ->property('name', 'trees.png')->end()
                      ->property('key', 'backup/2016-04-13/set2/trees.png')->end()
                    ->end()
                  ->end()
                ->end()
              ->end()
            ->end()
          ->end()
        ->end()

        ->key(2)
          ->property('name', 'root.txt')->end()
          ->property('type', 'file')->end()
        ->end()
     ;
  }

  public function testGettingAsSingleFileFromTree() {
    $gFile = $this->index->getFile('backup/2016-04-13/set2/trees.png');

    $this->assertInstanceOf(File::CLASS, $gFile);
    $this->assertEquals('/backup/2016-04-13/set2/trees.png', $gFile->getRelativePath());
    $this->assertEquals('set2', $gFile->directory->getName());
    $this->assertEquals('2016-04-13', $gFile->directory->parent->getName());
  }
}
