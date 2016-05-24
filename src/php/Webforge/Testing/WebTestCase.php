<?php

namespace Webforge\Testing;

use Liip\FunctionalTestBundle\Test\WebTestCase as LiipWebTestCase;
use Webforge\Testing\ObjectAsserter;
use Webmozart\Json\JsonDecoder;

class WebTestCase extends LiipWebTestCase {

  protected $credentials = array(
    'petra'=>array(
      'username' => 'petra.platzhalter@ps-webforge.net',
      'password' => 'secret'
    )
  );

  protected function loadAliceFixtures(Array $names, $client) {
    $dir = $GLOBALS['env']['root']->sub('tests/files/alice/');

    $files = array();
    foreach ($names as $name) {
      $files[] = $dir->getFile($name.'.yml');
    }

    $manager = $client->getContainer()->get('h4cc_alice_fixtures.manager');    
    $objects = $manager->loadFiles($files, 'yaml');

    $manager->persist($objects, true);
    return $objects;
  }

  protected function sendJsonRequest($client, $method, $url, $json = NULL) {
    return $client->request(
      $method,
      $url,
      array(),
      array(),
      array(
        'HTTP_ACCEPT'  => 'application/json',
        'CONTENT_TYPE'=>'application/json'
      ),
      $json
    );
  }

  protected function sendHtmlRequest($client, $method, $url, $body = NULL) {
    return $client->request(
      $method,
      $url,
      array(),
      array(),
      array(
        'HTTP_ACCEPT'  => 'text/html'
      ),
      $body
    );
  }

  protected function assertJsonResponse($statusCode, $client) {
    $this->assertStatusCode($statusCode, $client);

    $response = $client->getResponse();
    $content = (string) $response->getContent();

    if (empty($content)) return NULL;

    try {
        return new ObjectAsserter($this->parseJSON($content), $this);
    } catch (\Webmozart\Json\DecodingFailedException $e) {
      $this->fail('could not convert to json-response: '.$content);
    }
  }

  /**
   * @return mixed
   */
  public function parseJSON($string) {
    $decoder = new JsonDecoder();
    return $decoder->decode($string);
  }
}
