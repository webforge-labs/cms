<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\Process\Process;
use AppBundle\Entity\Binary;
use AppBundle\Entity\Image;

class MediaFileMetadataTest extends \Webforge\Testing\WebTestCase
{
    use \Webforge\Testing\TestTrait;

    protected function setupEmpty()
    {
        $client = self::makeClient($this->credentials['petra']);

        $this->loadAliceFixtures(
      array(
        'users'
      ),
      $client
    );

        return $client;
    }

    public function testMetadataCanBeStoredInBinary()
    {
        $client = $this->setupEmpty();

        $manager = $client->getContainer()->get('webforge.media.manager');
        $manager->beginTransaction();
        $binary = $manager->addFile('die-minis/', 'mini.png', $GLOBALS['env']['root']->sub('Resources/img/')->getFile('mini-single.png')->getContents());
        $manager->commitTransaction();

        $this->assertNull($binary->getMediaMetadata('thumbnails.xs'));

        $binary->setMediaMetadata('thumbnails.xs', $meta = (object) ['width'=>200, 'height'=>240]);

        $em = $client->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->flush();
        $em->clear();

        $binary = $em->getRepository(Binary::class)->findOneBy(array('id'=>$binary->getId()));

        $this->assertEquals(
      $meta,
      $binary->getMediaMetadata('thumbnails.xs'),
      'stored metadata'
    );
    }
}
