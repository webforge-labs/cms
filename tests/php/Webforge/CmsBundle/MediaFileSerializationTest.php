<?php

namespace Webforge\CmsBundle;

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

  public function testMediaControllerReturnsTheEmptyStructure() {
    $client = $this->setupEmpty();

    $manager = $client->getContainer()->get('webforge.media.manager');
    $manager->beginTransaction();
    $mediaFile = $manager->addFile('die-minis/', 'mini.png', $GLOBALS['env']['root']->sub('Resources/img/')->getFile('mini-single.png')->getContents());
    $manager->commitTransaction();

    $binary = new Binary();
    $binary->setMediaFileKey($mediaFile->getKey());

    $image = new Image();
    $image->setBinary($binary);
  }
  
}
