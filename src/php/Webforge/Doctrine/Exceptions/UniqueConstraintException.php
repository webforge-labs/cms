<?php

namespace Webforge\Doctrine\Exceptions;

use Webforge\Common\Preg;

class UniqueConstraintException extends Exception {
  
  /**
   * Name of the Constraint causing the PDOException
   *
   * @var string
   */
  public $uniqueConstraint;
  
  public function __construct($message, $code = 0, \Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
    $this->uniqueConstraint = Preg::qmatch($message, "/key\s+'(.*?)'/");
  }
}
