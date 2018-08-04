<?php

namespace Webforge\CmsBundle\Media;

use Symfony\Component\Process\Process;
use AppBundle\Entity\Binary;
use AppBundle\Entity\Image;
use Webforge\Symfony\ObjectGraph;
use Webforge\Testing\ObjectAsserter;

class MediaFileSerializationTest extends \Webforge\Testing\WebTestCase
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

    public function testSerializeAndUnseralizeTheNodeBuilder()
    {
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
            ->end();

        $nodeA = $builder->getNode();

        $dumper = new DumpVisitor(false);

        $str = serialize($nodeA);

        $nodeAUnserialized = unserialize($str);

        $this->assertEquals($dump = $nodeA->accept($dumper), $nodeAUnserialized->accept($dumper));
    }

    public function testTheBinaryEntityIsSerializedWithImageInformations()
    {
        $client = $this->setupEmpty();

        $image = $this->createImageWithTestBinary('/img/mini-single.png', $client);

        $this->assertThatSerializedImage($client, $image)
            ->property('id')->end()
            ->property('key')->isNotEmpty()->end()
            ->property('isExisting')->is(true)->end()
            ->property('thumbnails')->isObject()
            ->property('xs')->isObject()->end()
            ->property('sm')->isObject()->end()
            ->end();
    }

    /**
     * @dataProvider rotationImages
     */
    public function testBinaryEntityThumborMetadata_RespectsExifOrientations($imageName)
    {
        $client = $this->setupEmpty();

        $image = $this->createImageWithTestBinary($imageName, $client);

        $this->assertThatSerializedImage($client, $image)
            /* this is the gretchen question here... we won't assert that now */
            //->property('width', 1200)->end()
            //->property('height', 1800)->end()
            ->property('isExisting')->is(true)->end()
            ->property('thumbnails')->isObject()
                ->property('xs')->isObject()->end()
                // we expect thumbor to find all exif rotations correctly and display the thumbnail in the wanted rotation
                ->property('sm')->isObject() // only sm has metadata
                    // will be fitted in 620x620 so the longer side is 620 and the other side (width for portraits) is cutted
                    ->property('height', 620)->end()
                    ->property('width', 413)->end()
                ->end()
            ->end();
    }

    public function rotationImages()
    {
        return [
            ['exif-rotation/Portrait_1.jpg'],
            ['exif-rotation/Portrait_2.jpg'],
            ['exif-rotation/Portrait_3.jpg'],
            ['exif-rotation/Portrait_4.jpg'],
            ['exif-rotation/Portrait_5.jpg'],
            ['exif-rotation/Portrait_6.jpg'],
            ['exif-rotation/Portrait_7.jpg'],
            ['exif-rotation/Portrait_8.jpg']
        ];
    }

    public function testSerializingABrokenExifImage()
    {
        $client = $this->setupEmpty();

        $image = $this->createImageWithTestBinary('/img/broken-exif.jpg', $client);

        // boring assertions, it should "run through"
        $this->assertThatSerializedImage($client, $image)
            ->property('thumbnails')
                ->property('sm')
                    ->property('isPortrait', true)->end()
                    ->property('height', 620)->end();
    }

    /**
     * @param $imageName
     * @param $client
     * @return Image
     */
    protected function createImageWithTestBinary($imageName, $client): Image
    {
        $manager = $client->getContainer()->get('webforge.media.manager');
        $manager->beginTransaction();
        $binary = $manager->addFile(
            dirname($imageName),
            basename($imageName),
            $GLOBALS['env']['root']->sub('tests/files/')->getFile($imageName)->getContents()
        );
        $manager->commitTransaction();

        $image = new Image();
        $image->setBinary($binary);
        return $image;
    }

    /**
     * @param $client
     * @param $image
     * @return ObjectAsserter
     */
    protected function assertThatSerializedImage($client, $image): ObjectAsserter
    {
        $og = new ObjectGraph($client->getContainer()->get('jms_serializer'));

        return $this->assertThatObject($og->serialize($image));
    }
}
