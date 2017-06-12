<?php

namespace Webforge\CmsBundle\Content;

use Webmozart\Json\JsonDecoder;

class BlocksExtendingTest extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase {

  use \Webforge\Testing\TestTrait;

  private $blocks, $container;

  public function setUp() {
    parent::setUp();
    $this->bootKernel();

    $this->container = self::$kernel->getContainer();

    $this->blocks = $this->container->get('webforge.content.blocks');
  }

  public function testItExtendsSimpleBlocksThatHaveMarkdown() {
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

  public function testItExtendsBlocksWithCompoundProperties() {
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

  /**
   * @return mixed
   */
  public function parseJSON($string) {
    $decoder = new JsonDecoder();
    return $decoder->decode($string);
  }
}