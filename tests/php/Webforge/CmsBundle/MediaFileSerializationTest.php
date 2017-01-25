<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\Process\Process;
  
class MediaFileSerializationTest extends \Webforge\Testing\WebTestCase {

  use \Webforge\Testing\TestTrait;

  protected function setupEmpty() {
    $client = self::makeClient($this->credentials['petra']);

    $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

    return $client;
  }

  public function testDiscoverTree() {

    $builder = new \Tree\Builder\NodeBuilder;

    $builder
        ->value('A')
        ->leaf('B')
        ->tree('C')
            ->tree('D')
                ->leaf('G')
                ->leaf('H')
                ->end()
            ->leaf('E')
            ->leaf('F')
            ->end()
    ;

    $nodeA = $builder->getNode();

    $dumper = new DumpVisitor();

    $str = serialize($nodeA);

    $nodeAUnserialized = unserialize($str);

    $this->assertEquals($dump = $nodeA->accept($dumper), $nodeAUnserialized->accept($dumper));
  }

  public function testTheBinaryEntityIsSerializedWithImageInformations() {
    $client = $this->setupEmpty();

    $manager = $client->getContainer()->get('webforge.media.manager');
    $manager->beginTransaction();
    $mediaFile = $manager->addFile('die-minis/', 'mini.png', $GLOBALS['env']['root']->sub('Resources/img/')->getFile('mini-single.png')->getContents());
    $manager->commitTransaction();

    $binary = new Binary();
    $binary->setMediaFileKey($mediaFile->getKey());

    $image = new Image();
    $image->setBinary($binary);

    $this->markTestIncomplete('this should check if serialization works');
  }
  
}
