<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * 
 * 
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\Entity
 * @ORM\Table(name="images")
 * @Serializer\ExclusionPolicy("all")
 */
class Image extends CompiledImage {
}
