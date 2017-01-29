<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * 
 * 
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\Entity
 * @ORM\Table(name="binaries")
 * @Serializer\ExclusionPolicy("all")
 */
class Binary extends CompiledBinary implements \Webforge\CmsBundle\Model\MediaFileEntityInterface {
}
