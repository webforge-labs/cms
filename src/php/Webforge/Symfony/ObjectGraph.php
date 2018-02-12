<?php

namespace Webforge\Symfony;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ObjectGraph {

  protected $serializer;

  public function __construct(SerializerInterface $serializer) {
    $this->serializer = $serializer;
  }

  /**
   * Serializes a complex object/array/collection to scalar values
   * @param  mixed $object
   * @param  Array|null $groups the serialization groups as strings used for the export (if empty: no filters are applied)
   * @return scalar
   */
  public function serialize($object, Array $groups = NULL) {
    $context = new SerializationContext();

    if (isset($groups)) {
      $context->setGroups($groups);
    }
    $context->setSerializeNull(true);

    return json_decode($this->serializer->serialize($object, 'json', $context));
  }
}
