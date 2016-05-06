<?php

namespace Webforge\CmsBundle\Serialization;

use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;

/**
 * Converts some spcial interfaced entities to serializiation types
 *
 * If an object(entity) is passed with an mapped interface then the mapped type is returned
 * The type is used for serialization then
 */
class SpecialTypesListener implements EventSubscriberInterface {

  private $mappings;

  public function __construct($mappings) {
    $this->mappings = $mappings;
  }

  public static function getSubscribedEvents() {
    return array(
      array('event' => 'serializer.pre_serialize', 'method' => 'onSerializerPreserialize'),
    );
  }

  public function onSerializerPreserialize(PreSerializeEvent $event)  {
    foreach ($this->mappings as $interface => $type) {
      if ($event->getObject() instanceof $interface) {
        $event->setType($type);
        return;
      }
    }
  }
}
