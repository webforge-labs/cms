<?php

namespace %project.bundle_namespace%\Test;

class ControllerTestCase extends \Webforge\Testing\WebTestCase {

  protected $client;

  protected function setUpFixtures(Array $fixtures) {
    $this->client = $this->makeClient($this->credentials['petra']);

    $this->loadAliceFixtures($fixtures, $this->client);
  }
}
