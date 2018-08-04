<?php

namespace Webforge\Doctrine\Exceptions;

use PDOException;
use Webforge\Common\Preg;

class PDOExceptionHandler
{
    protected $exceptions = array(
    'HY000'=>'Webforge\Doctrine\Exceptions\ForeignKeyConstraintException',
    '23000'=>'Webforge\Doctrine\Exceptions\UniqueConstraintException',
    '08004'=>'Webforge\Doctrine\Exceptions\TooManyConnectionsException',
    '42S22'=>'Webforge\Doctrine\Exceptions\UnknownColumnException'
  );

    public function convert(PDOException $e)
    {
        // e.g.: SQLSTATE[08004] Too many connections: 1040
        if (Preg::match($e->getMessage(), '/SQLSTATE\[([0-9A-Za-z]+)\][\s:](.*)?/s', $m)) {
            list($match, $code, $message) = $m;
        } elseif (isset($e->errorInfo)) {
            list($code, $numericCode, $message) = $e->errorInfo;
        } else {
            return $e;
        }

        if (array_key_exists($code, $this->exceptions)) {
            $fqn = $this->exceptions[$code];
            return new $fqn($message, isset($numericCode) ? (int) $numericCode : 0, $e);
        }
    
        return $e;
    }
}
