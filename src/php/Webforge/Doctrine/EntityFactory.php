<?php

namespace Webforge\Doctrine;

use ReflectionClass;
use InvalidArgumentException;
use Webforge\Common\ClassUtil;

/**
 * 
 * 
 * caveats:
 *   - creates setter as set.ucfirst($propertyName) (which might be wrong)
 *   - constructor fields aren't checked or parsed if they are required
 * 
 * @TODO use an EntityReflection(Service) to decouple from the ReflectionCLass (to find the Constructor)  and to find the name of the setter
 */
class EntityFactory {

  /**
   * @var string
   */
  protected $fqn;

  protected $reflection;

  /**
   * Cache for the names of the constructor
   *
   * @var string[]
   */
  protected $constructorFields;

  public function __construct($entityFQN) {
    $this->fqn = $entityFQN;
  }

  /**
   * Creates a new Entity with the fields as value
   * 
   * it automatically uses the constructor the right way, so that most parameters will be given through constructor
   * @param object|array $fields the key is the name of a property nd the value is the value of the property in the created entity
   */
  public function create($fields) {
    $fields = (array) $fields;

    $params = array();
    foreach ($this->getConstructorFields() as $propertyName) {
      if (!array_key_exists($propertyName, $fields)) {
        $params[] = NULL;
      } else {
        $params[] = $fields[$propertyName];
        unset($fields[$propertyName]);
      }
    }

    $entity = ClassUtil::newClassInstance($this->getReflection(), $params);

    foreach ($fields as $propertyName => $value) {
      $setter = 'set'.ucfirst($propertyName);
      $entity->$setter($value);
    }

    return $entity;
  }

  /**
   * Returns the name of the fields that need or can be given with the constructor of the entity
   * 
   * @return string[]
   */
  protected function getConstructorFields() {
    if (!isset($this->constructorFields)) {
      $this->constructorFields = array();
      
      $constructor = $this->getReflection()->getConstructor();

      foreach ($constructor->getParameters() as $rParameter) {
        $this->constructorFields[] = $rParameter->getName();
      }
    }
    
    return $this->constructorFields;
  }

  protected function getReflection() {
    if (!isset($this->reflection)) {
      $this->reflection = new ReflectionClass($this->fqn);
    }

    return $this->reflection;
  }
}
