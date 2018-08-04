<?php

namespace Webforge\CmsBundle\Entity;

use Webforge\Common\DateTime\DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 *
 *
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\MappedSuperClass
 * @Serializer\ExclusionPolicy("all")
 */
abstract class User extends CompiledUser
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDisplayName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }
}
