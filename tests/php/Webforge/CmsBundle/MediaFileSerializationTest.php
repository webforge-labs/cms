<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\Process\Process;
use AppBundle\Entity\Binary;
use AppBundle\Entity\Image;
  
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

  public function testSerializeAndUnseralizeTheNodeBuilder() {

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

    $dumper = new DumpVisitor(FALSE);

    $str = serialize($nodeA);

    $nodeAUnserialized = unserialize($str);

    $this->assertEquals($dump = $nodeA->accept($dumper), $nodeAUnserialized->accept($dumper));
  }

  public function testTheBinaryEntityIsSerializedWithImageInformations() {
    $client = $this->setupEmpty();

    $manager = $client->getContainer()->get('webforge.media.manager');
    $manager->beginTransaction();
    $binary = $manager->addFile('die-minis/', 'mini.png', $GLOBALS['env']['root']->sub('Resources/img/')->getFile('mini-single.png')->getContents());
    $manager->commitTransaction();

    $image = new Image();
    $image->setBinary($binary);

    $og = new \Webforge\Symfony\ObjectGraph($client->getContainer()->get('jms_serializer'));
    $this->assertThatObject($og->serialize($image))
      ->property('id')->end()
      ->property('key')->isNotEmpty()->end()
      ->property('isExisting')->is(TRUE)->end()
      ->property('thumbnails')->isObject()
        ->property('xs')->isObject()->end()
        ->property('sm')->isObject()->end()
      ->end()
    ;
  }
}
