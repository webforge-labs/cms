<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Compiled Entity for AppBundle\Entity\Binary
 *
 * To change table name or entity repository edit the AppBundle\Entity\Binary class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledBinary
{
  
  /**
   * id
   * @var integer
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   * @Serializer\Expose
   * @Serializer\Type("integer")
   */
    protected $id;
  
    /**
     * mediaFileKey
     * @var string
     * @ORM\Column
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    protected $mediaFileKey;
  
    /**
     * mediaName
     * @var string
     * @ORM\Column
     * @Serializer\Expose
     * @Serializer\Type("string")
     */
    protected $mediaName;
  
    /**
     * mediaMeta
     * @var object
     * @ORM\Column(type="object", nullable=true)
     * @Serializer\Expose
     * @Serializer\Type("")
     */
    protected $mediaMeta;
  
    public function __construct()
    {
    }
  
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
  
    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
  
    /**
     * @return string
     */
    public function getMediaFileKey()
    {
        return $this->mediaFileKey;
    }
  
    /**
     * @param string $mediaFileKey
     */
    public function setMediaFileKey($mediaFileKey)
    {
        $this->mediaFileKey = $mediaFileKey;
        return $this;
    }
  
    /**
     * @return string
     */
    public function getMediaName()
    {
        return $this->mediaName;
    }
  
    /**
     * @param string $mediaName
     */
    public function setMediaName($mediaName)
    {
        $this->mediaName = $mediaName;
        return $this;
    }
  
    /**
     * @return object
     */
    public function getMediaMeta()
    {
        return $this->mediaMeta;
    }
  
    /**
     * @param object $mediaMeta
     */
    public function setMediaMeta(\stdClass $mediaMeta = null)
    {
        $this->mediaMeta = $mediaMeta;
        return $this;
    }
}
