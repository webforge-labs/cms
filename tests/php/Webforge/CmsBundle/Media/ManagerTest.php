<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\Process\Process;
use AppBundle\Entity\Binary;
use AppBundle\Entity\Image;
  
class MediaFileMetadataTest extends \Webforge\Testing\WebTestCase {

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

  public function testManagerCanReturnGaufretteStreamUrls() {
    $client = $this->setupEmpty();

    $manager = $client->getContainer()->get('webforge.media.manager');
    $manager->beginTransaction();
    $entity = $manager->addFile('die-minis/', 'mini.png', $contents = $GLOBALS['env']['root']->sub('Resources/img/')->getFile('mini-single.png')->getContents());
    $manager->commitTransaction();

    $this->assertNotEmpty($url = $manager->getStreamUrl($entity), 'url should be returned');
    $this->assertIsReadable($url, 'the stream url should be readable');

    $this->assertEquals($contents, file_get_contents(($url)), 'the contents of the stream url do not match the file contents originally stored');
  }
}
