<?php

namespace Webforge\Testing;

use Webforge\Common\System\Dir;
use Psr\Http\Message\MessageInterface as Message;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Psr7\str;
use GuzzleHttp\Psr7\parse_request;
use GuzzleHttp\Psr7\parse_response;
use InvalidArgumentException;

class HttpRecorder {

  protected $baseDirectory;

  public function __construct(Dir $baseDirectory) {
    $this->dir = $baseDirectory;
  }

  /**
   * Saves the passed message in the directory structure
   * 
   * @param  Message $message can be either request or response
   * @param  string  $name can have a relative part (forward slashes) for sub-directories
   * @return File
   */
  public function store(Message $message, $name) {
    $type = $this->getType($message);
    $file = $this->dir->getFile($name.'.'.$type);
    $file->getDirectory()->create();

    $file->writeContents(\GuzzleHttp\Psr7\str($message));

    return $file;
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
