<?php

namespace AppBundle\Entity;

use Webforge\Common\DateTime\DateTime;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * 
 * 
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\Entity
 * @ORM\Table(name="media_trees")
 * @Serializer\ExclusionPolicy("all")
 */
class MediaTree extends CompiledMediaTree {
}
