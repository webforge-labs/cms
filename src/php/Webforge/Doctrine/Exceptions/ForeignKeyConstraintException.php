<?php

namespace Webforge\Doctrine\Exceptions;

class ForeignKeyConstraintException extends Exception
{
    public $foreignKey; // @TODO not parsed
}
