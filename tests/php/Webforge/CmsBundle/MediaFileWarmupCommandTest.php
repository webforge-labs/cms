<?php

namespace Webforge\CmsBundle\Media;

use AppBundle\Entity\Binary;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class MediaFileWarmupCommandTest extends \Webforge\Testing\WebTestCase
{
    use \Webforge\Testing\TestTrait;

    protected function setupEmpty()
    {
        $client = self::makeClient($this->credentials['petra']);

        $this->loadAliceFixtures(
            [
                'users'
            ],
            $client
        );

        return $client;
    }

    /**
     * @dataProvider provideFiles
     */
    public function testMetadataWillBeCreatedWhenCommandIsRun($file)
    {
        $client = $this->setupEmpty();

        $manager = $client->getContainer()->get('webforge.media.manager');
        $manager->beginTransaction();
        /** @var Binary $binary */
        $binary = $manager->addFile('test-images/', $file, $GLOBALS['env']['root']->sub('Resources/img/')->getFile($file)->getContents());
        $manager->commitTransaction();

        $this->assertNull($binary->getMediaMetadata('thumbnails.sm'));

        $application = new Application($client->getKernel());
        $command = $application->find('cms:warmup-media-file');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'mediaKey' => $binary->getMediaFileKey()
        ));

        $this->assertEquals(0, $commandTester->getStatusCode());

        $em = $client->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->clear();

        $binary = $em->getRepository(Binary::class)->findOneBy(['id' => $binary->getId()]);

        $this->assertNotNull(
            $binary->getMediaMetadata('thumbor.sm'),
            'stored metadata should have been warmed up. Output from command: '.$commandTester->getDisplay()
        );
    }

    public function provideFiles()
    {
        yield ['background.jpg'];
        yield ['mini-single.png'];
    }
}
