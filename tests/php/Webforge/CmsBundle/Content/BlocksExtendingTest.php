<?php

namespace Webforge\CmsBundle\Content;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webmozart\Json\JsonDecoder;

class BlocksExtendingTest extends KernelTestCase
{
    use \Webforge\Testing\TestTrait;

    private $blocks;

    public function setUp()
    {
        parent::setUp();

        if (!static::$container) {
            static::bootKernel();
        }

        // depends on etc/cms/blocktypes.json
        // depends on etc/symfony/parts/images.yml
        $this->blocks = static::$container->get('webforge.content.blocks');
        $this->mediaManager = static::$container->get('webforge.media.manager');
    }

    public function testItExtendsSimpleBlocksThatHaveMarkdown()
    {
        $cs = $this->parseJSON('{
    "blocks": [
      {
        "type": "intro",
        "label": "Introtext",
        "uuid": "9105585b-e9a2-4e87-87e3-20ee09015f07",
        "intro": "Die Montpelliérains werden mit 300 Sonnentagen im Jahr **verwöhnt**. Eine mehrtägige Wolkendecke wird mit Kopfschütteln kommentiert."
      },
      {
        "type": "fulltext",
        "label": "Fließtext",
        "markdown": "# Tu veux aller dehors?",
        "uuid": "e85eeace-fa11-4e46-a89e-0caa963ffbb3"
      }
    ]
    }');

        $this->blocks->extendContentStream($cs, new \stdClass);

        $this->assertThatObject($cs)
            ->property('blocks')->isArray()
                ->key(0)
                    ->property('introHtml')->contains('<strong>verwöhnt</strong>')->end()
                ->end()
                ->key(1)
                    ->property('markdownHtml')->contains('<h1>Tu veux aller dehors?</h1>')->end()
                ->end();
    }

    public function testItExtendsBlocksWithCompoundProperties()
    {
        $cs = $this->parseJSON('{
      "blocks": [
        {
          "type": "interview",
          "label": "Frage",
          "uuid": "6896a30a-2ecd-4bb9-a334-334f6783a31d",
          "answer": "**Nichts**",
          "question": "## Was würdest Du in deinem Leben ändern?"
        }
      ]
    }');

        $this->blocks->extendContentStream($cs, new \stdClass);

        $this->assertThatObject($cs)
            ->property('blocks')->isArray()
            ->key(0)
            ->property('answerHtml')->contains('<strong>Nichts</strong>')->end()
            ->property('questionHtml')->contains('<h2>Was würdest Du in deinem Leben ändern?</h2>')->end()
            ->property('questionText')->is($this->logicalNot($this->stringContains('<h2>')))->end()
            ->end();
    }

    public function testItExtendsImagesWithFreshThumbnailAndUrlInformation()
    {
        $cs = $this->parseJSON('{
      "blocks": [
        {
          "type": "section-leftimage",
          "label": "Abschnitt mit Bild links",
          "uuid": "7596a30a-2ecd-4bb9-a334-334f6783a31d",
          "rightContent": "**Nichts**",
          "color": "mint",
          "screenshots": [
              {
                  "type": "file",
                  "items": [],
                  "name": "demo-home-monitor.png",
                  "key": "fc6872e9-d7b8-499c-89c7-0101b91e3f25",
                  "url": "\/cms\/media?download=1&file=fc6872e9-d7b8-499c-89c7-0101b91e3f25",
                  "mimeType": "image\/png",
                  "isExisting": true,
                  "thumbnails": {
                      "xs": {
                          "isPortrait": false,
                          "isLandscape": false,
                          "orientation": "landscape",
                          "url": "http:\/\/live.com\/images\/cache\/xs\/fc6872e9-d7b8-499c-89c7-0101b91e3f25\/demo-home-monitor.png",
                          "name": "xs"
                      },
                      "sm": {
                          "isPortrait": false,
                          "isLandscape": true,
                          "orientation": "landscape",
                          "url": "http:\/\/live.com\/images\/cache\/sm\/fc6872e9-d7b8-499c-89c7-0101b91e3f25\/demo-home-monitor.png",
                          "name": "sm"
                      },
                      "inpage1000": {
                          "isPortrait": false,
                          "isLandscape": true,
                          "orientation": "landscape",
                          "url": "http:\/\/live.com\/images\/cache\/inpage1000\/fc6872e9-d7b8-499c-89c7-0101b91e3f25\/demo-home-monitor.png",
                          "name": "inpage1000"
                      }
                  }
              }
          ]
        }
      ]
    }');

        // we need to create the file physically
        try {
            $this->mediaManager->beginTransaction();
            $screenshot = $this->mediaManager->addFile('/_seiten/home', 'screenshot-demo.jpg',
                $this->getResourceImage('background.jpg')->getContents());
            $mediaKey = $screenshot->getMediaFileKey();
            $this->mediaManager->commitTransaction();
        } catch (\ Webforge\CmsBundle\Media\FileAlreadyExistsException $e) {
            $mediaKey = $e->mediaKey;
        }

        $cs->blocks[0]->screenshots[0]->key = $mediaKey;

        $this->blocks->extendContentStream($cs, new \stdClass);

        $this->assertThatObject($cs)
            ->property('blocks')->isArray()
                ->key(0)
                    ->property('screenshots')->isArray()
                        ->key(0)
                            ->property('thumbnails')->isArray()// i think this is an array because of the stupid serializer, but okay
                                ->key('sm')->end()
                                ->key('gallery')->end()// this was NOT in the saved image thumbnails, but it IS defined in etc/symfony/parts/images.yml
                                ->key('xs')
                                    ->property('url')
                                        ->is($this->logicalNot($this->stringContains('live.com')))
                                        ->is($this->stringContains('screenshot-demo.jpg'))
                                    ->end()
                                ->end()
                            ->end();
    }

    /**
     * @return mixed
     */
    public function parseJSON($string)
    {
        $decoder = new JsonDecoder();
        return $decoder->decode($string);
    }

    protected function getResourceImage($filename)
    {
        return $GLOBALS['env']['root']->sub('Resources/img/')->getFile($filename);
    }
}