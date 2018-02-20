<?php

namespace Webforge\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Webforge\Common\ClassUtil;
use LogicException;
use RuntimeException;
use Closure;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Synchronizes two collections (one hydrated from database, one given as a detached $toCollection)
 * 
 * the $fromCollection is the previous selected set of entities
 * the $toCollection is a set of new entities. 
 * After synchronizing:
 *   - the entities in $fromCollection but not in $toCollection are deleted
 *   - the entities in $fromCollection and in $toCollection are updated (merged)
 *   - the entities in $toCollection but not in $fromCollection are inserted
 * 
 */
class CollectionSynchronizer {
  
  protected $uniqueConstraints = array(array('id'));

  protected $repository;
  protected $factory;
  protected $em;

  /**
   * @var Closure
   */
  protected $adder, $remover, $setter, $creater, $merger;

  protected $queryBuilder, $binds;

  /**
   * @param string $entityFQN provide the FQN of the entity where the $fromCollection is in
   * @param string $collectionProperty provide the name of the property in the class from $entityFQN which is passed as $fromCollection
   */
  public static function createFor($entityFQN, $collectionProperty, EntityManager $em, Array $options = array()) {
    $entityMeta = $em->getMetaDataFactory()->getMetadataFor($entityFQN);
    $mapping = $entityMeta->getAssociationMapping($collectionProperty);

    $collectionEntityFQN = $mapping['targetEntity'];

    $isOneToMany = $mapping['type'] === ClassMetadata::ONE_TO_MANY;

    if (!$mapping['isOwningSide'] && !$isOneToMany) {
      throw new LogicException(
        sprintf('The collection %s::%s is not the owningSide from the assocation %s <=> %s. Synchronizing this side won\'t work', $entityFQN, $collectionProperty, $entityFQN, $collectionEntityFQN)
      );
    }

    $adder = $remover = NULL;
    if ($isOneToMany) {
      $set = 'set'.ucfirst($mapping['mappedBy']);

      $adder = function ($entity, $collectionEntity, $toObject) use ($set) {
        $collectionEntity->$set($entity);
      };


      if (isset($options['deleteOnManySide'])) {
        $remover = function ($entity, $collectionEntity) use ($set, $em) {
          $shortName = ClassUtil::getClassName(get_class($collectionEntity));
          $remover = 'remove'.$shortName;

          $entity->$remover($collectionEntity);

          $em->remove($collectionEntity);
        };
      } else {
        $remover = function ($entity, $collectionEntity) use ($set) {
          $collectionEntity->$set(NULL);
        };
      }
    }

    // @TODO we could read the unique constraints from the entityMeta of the $collectionEntityFQN
    // @TODO we could read the identifier from the entityMeta of the collectionEntityFQN
    // getIdentifierFieldNames()

    $synchronizer = new static(
      $em,
      $em->getRepository($collectionEntityFQN),
      new EntityFactory($collectionEntityFQN),
      $adder,
      $remover
    );

    return $synchronizer;
  }

  /**
   * @param $repository the entity repository for the entity in $fromCollection
   * @param $factory a factory to create inserted items in toCollection
   */
  protected function __construct(EntityManager $em, EntityRepository $repository, EntityFactory $factory, Closure $adder = NULL, Closure $remover = NULL, Closure $setter = NULL, Closure $merger = NULL, Closure $creater = NULL) {
    $this->em = $em;
    $this->repository = $repository;
    $this->factory = $factory;

    if (!isset($adder)) {
      $adder = function ($entity, $collectionEntity, $toObject) {
        $shortName = ClassUtil::getClassName(get_class($collectionEntity));
        $adder = 'add'.$shortName;
        $entity->$adder($collectionEntity);
      };
    }
    $this->adder = $adder;

    if (!isset($remover)) {
      $remover = function ($entity, $collectionEntity) {
        $shortName = ClassUtil::getClassName(get_class($collectionEntity));
        $remover = 'remove'.$shortName;
        $entity->$remover($collectionEntity);
      };
    }
    $this->remover = $remover;

    if (!isset($setter)) {
      $setter = function ($collectionEntity, $property, $value) {
        $setter = 'set'.ucfirst($property);
        $collectionEntity->$setter($value);
      };
    }
    $this->setter = $setter;

    if (!isset($merger)) {
      $merger = function ($entity, $collectionEntity, $toObject) use ($setter) {
        foreach ($toObject as $property => $value) {
          if ($property === 'id') continue; // dont update id in any case

          $setter($collectionEntity, $property, $value);
        }
      };
    }
    $this->merger = $merger;

    if (!isset($creater)) {
      $creater = function($toObject, $entity, $toCollectionKey) use ($factory) {
        return $factory->create($toObject);
      };
    }
    $this->creater = $creater;
  }

  public function addUniqueConstraint(Array $fieldNames) {
    $this->uniqueConstraints[] = $fieldNames;
  }

  public function process($entity, $fromCollection, $toCollection) {
    // clone fromCollection because this might be modified while insert/delete events
    $fromCollectionCopy = $fromCollection instanceof \Doctrine\Common\Collections\Collection ? $fromCollection->toArray() : (array) $fromCollection;

    $updates = $inserts = $deletes = array();
    $index = array();
    foreach ($toCollection as $toCollectionKey => $toObject) {
      $fromObject = $this->hydrateUniqueObject($toObject, $toCollectionKey, $entity);
      
      if ($fromObject === NULL) {
        $inserts[] = $this->insert($entity, $toObject, $toCollectionKey);

        // inserts do not have to be indexed, they cannot be in $fromCollection (because fromCollection is from universe and hydrateUniqueObject searches in universe)
      } else {
        // an matching object was found by the data from $toObject
        $updates[] = $this->merge($entity, $fromObject, $toObject, $toCollectionKey);
        
        $index[$this->hashObject($fromObject)] = TRUE;
      }
    }
    
    foreach ($fromCollectionCopy as $fromObject) {
      if (!array_key_exists($this->hashObject($fromObject), $index)) { // object is not an insert or not an update
        $deletes[] = $this->delete($entity, $fromObject);
      }
    }

    return array($inserts, $updates, $deletes);
  }

  /**
   * The object is not $fromCollection and new in $toCollection
   */
  protected function insert($entity, $toObject, $toCollectionKey) {
    $creater = $this->creater;
    $insertedEntity = $creater($toObject, $entity, $toCollectionKey);

    $this->em->persist($insertedEntity);

    $adder = $this->adder;
    $adder($entity, $insertedEntity, $toObject, $toCollectionKey);
  }

  /**
   * The $fromObject should be updated with the values stored in $toObject
   * 
   * notice that the element $fromObject does not necessariliy has to be already in the collection of $entity, because it is just hydrated from the universe
   */
  protected function merge($entity, $fromObject, $toObject, $toCollectionKey) {
    $merge = $this->merger;
    $merge($entity, $fromObject, $toObject, $toCollectionKey);

    $adder = $this->adder;
    $adder($entity, $fromObject, $toObject, $toCollectionKey);
  }

  /**
   * The object is in $fromCollection but not found in $toCollection
   */
  protected function delete($entity, $fromObject) {
    $remover = $this->remover;
    $remover($entity, $fromObject);
  }
  
  /**
   * Finds a matching object from the $toObject in $toCollection which is stored in the universe of the $fromCollection
   *
   * @returns NULL if no object is found
   */
  protected function hydrateUniqueObject($toObject, $toCollectionKey, $entity) {
    if (isset($this->hydrator)) {
      $hydrate = $this->hydrator;
      return $hydrate($toObject, $this->repository, $entity, $toCollectionKey);
    } else {
      return $this->hydrateByUniqueConstraints($toObject, $toCollectionKey);
    }
  }
  
  /**
   * Sets the mechanism to retrieve synched collection-entities
   * 
   * @param Closure $hydrator function(array|stdClass $toObject, $repository from the collectionEntity, $entity, $toCollectionKey)
   */
  public function setHydrator(Closure $hydrator) {
    $this->hydrator = $hydrator;
  }

  /**
   * Sets the adder procedure
   * 
   * @param Closure $adder function ($entity, $collectionEntity, array|stdClass $toObject) where $collectionEntity is an entity that is added to the synchronzied collection in $entity
   */
  public function setAdder(Closure $adder) {
    $this->adder = $adder;
  }

  /**
   * Set the create procedure
   *
   * creates a new entity for the collection
   * @param Closure $creater function (stdClass $toObject, $entity) should return the entity for the collection of $entity created from $toObject
   */
  public function setCreater(Closure $creater) {
    $this->creater = $creater;
  }

  /**
   * Set the remover procedure
   * 
   * @param Closure $remover function ($entity, $collectionEntity) should remove $collectionEntity from the synchronized collection of $entity
   */
  public function setRemover(Closure $remover) {
    $this->remover = $remover;
  }

  /**
   * Sets the merger procedure
   * 
   * @param Closure $merger function ($entity, $collectionEntity, stdClass $toObject) should merge the infos in $toObject to $collectionEntity
   * it is not necesserary to add the collectionEntity in $entity
   */
  public function setMerger(Closure $merger) {
    $this->merger = $merger;
  }
  
  /**
   * Hashes an object from the universe from $fromCollection
   *
   * notice that new elements do not need a hash (the return value does not matter)
   * @return scalar
   */
  protected function hashObject($fromObject) {
    return $fromObject->getId();
  }

  /**
   * @return entity|NULL
   */
  protected function hydrateByUniqueConstraints($toObject, $toCollectionKey) {
    if (!isset($this->queryBuilder)) {
      $qb = $this->repository->createQueryBuilder('entity');
    
      /* 
        create an or for every uniqueConstraint:

        $qb->where($qb->expr()->orX(
          uniqueConstraintExpression,
          uniqueConstraintExpression
        ));

        like:
         
        $qb->where($qb->expr()->orX(
          $qb->expr()->eq('tag.label', ':label'),
          $qb->expr()->eq('tag.id', ':id')
        ));
      
        (id is an unique constraint and label is an unique constraint)
      */

      $conditions = $qb->expr()->orX();
      $binds = array();

      foreach ($this->uniqueConstraints as $fields) {
        $constraint = $qb->expr()->andX();

        foreach ($fields as $field) {
          $binds[$field] = TRUE;
          $constraint->add(
            $qb->expr()->eq('entity.'.$field, ':'.$field)
          );
        }

        $conditions->add($constraint);
      }

      $qb->where($conditions);

      $this->queryBuilder = $qb;
      $this->binds = array_keys($binds);
    } 

    $parameters = array();
    foreach ($toObject as $key => $value) {
      $parameters[$key] = $value;
    }

    if (count($parameters) != count($this->binds)) {
      throw new RuntimeException(
        sprintf("The number of parameters needed for a unique constraint query, does not match for key '%s' in the toCollection. The Query is:\n", $toCollectionKey).
        $this->queryBuilder->getDQL()."\n".
        'needed parameters: '.implode(', ', $this->binds)."\n".
        'given parameters: '.implode(', ', array_keys($parameters))
      );
    }

    $query = $this->queryBuilder->getQuery();
    $query->setParameters($parameters);

    try {
      return $query->getSingleResult();
    } catch (\Doctrine\ORM\NoResultException $e) {
      return NULL;
    }
  }
}
