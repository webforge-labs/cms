<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 *
 *
 * this entity was compiled from Webforge\Doctrine\Compiler
 * @ORM\Entity
 * @ORM\Table(name="binaries")
 * @Serializer\ExclusionPolicy("all")
 */
class Binary extends CompiledBinary implements \Webforge\CmsBundle\Model\MediaFileEntityInterface
{
    use \Webforge\CmsBundle\Media\MediaFileEntityMetadata;
}
