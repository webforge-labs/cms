<?php

class MainControllerTest extends \%project.bundle_namespace%\Test\ControllerTestCase {

  public function setUp() {
    parent::setUp();

    $this->setUpFixtures(
      [
        'users'
      ]
    );

    $this->client->followRedirects();
  }

  public function testViewingTheHomepage() {
    $crawler = $this->sendHtmlRequest($this->client, 'GET', '/');
    $this->assertStatusCode(200, $this->client);

    $this->assertEquals('heyho', $crawler->filter('h2')->text());
    $this->assertContains('geschrieben von', $crawler->filter('p.author')->text());
    $this->assertContains('Copyright Webforge', $crawler->filter('footer')->text());
  }
}
