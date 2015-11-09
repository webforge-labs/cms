<?php

namespace Webforge\Testing;

use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class GuzzleMocker {

  public function __construct() {
    $this->mockHandler = new MockHandler();

    $this->handlerStack = HandlerStack::create($this->mockHandler);
  }

  public function createClient(Array $options) {
    $options['handler'] = $this->handlerStack;

    return new Client($options);
  }

  /**
   * Loads an Response into the queue
   * 
   * @param  Message $message can be either request or response
   * @param  string  $name can have a relative part (forward slashes) for sub-directories
   * @return File
   */
  public function enqueueResponse(Response $response) {
    $this->mockHandler->append($response);
  }

  /**
   * @return Psr\Http\Message\RequestInterface
   */
  public function loadRequest($name) {
    $file = $this->dir->getFile($name.'.request');

    return \GuzzleHttp\Psr7\parse_request($file->getContents());
  }

  /**
   * @return Psr\Http\Message\ResponseInterface
   */
  public function loadResponse($name) {
    $file = $this->dir->getFile($name.'.response');

    return \GuzzleHttp\Psr7\parse_response($file->getContents());
  }

  /**
   * @return string request or response
   */
  private function getType(Message $message) {
    if ($message instanceof Request) {
      $type = 'request';
    } elseif ($message instanceof Response) {
      $type = 'response';
    } else {
      throw new InvalidArgumentException('$message should be an response or an request');
    }

    return $type;
  }
}
