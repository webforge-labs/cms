<?php

namespace Webforge\Gaufrette;

use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;
use Webforge\Testing\ObjectAsserter;

class GaufretteIndexTest extends \PHPUnit_Framework_TestCase {

  public function testGettingAsTree() {
    
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

    $index = new Index($filesystem);

    $object = new ObjectAsserter($index->asTree(), $this);

    $object->property('name')->end()
      ->property('type', 'ROOT')->end()
      ->property('items')->isArray()->length(3)
        ->key(0)
          ->property('name', '2016-04-13')->end()
          ->property('items')->isArray()->length(3)
            ->key(0)
              ->property('name', 'file1.jpg')->end()
            ->end()
            ->key(1)
              ->property('name', 'file2.jpg')->end()
            ->end()
            ->key(2)
              ->property('name', 'set2')->end()
              ->property('type', 'directory')->end()
              ->property('items')->isArray()->length(1)
                ->key(0)
                  ->property('name', 'trees.png')->end()
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
}
