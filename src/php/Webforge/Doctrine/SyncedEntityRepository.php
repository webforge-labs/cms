<?php

namespace Webforge\Doctrine;

use stdClass;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

class SyncedEntityRepository
{
    protected $fqn;

    protected $uniqueFields;

    protected $entityFactory;

    protected $cache = array();

    protected $em;
  
    protected $repository;

    /**
     * @param array $uniqueFields every name of the unique field should point to a scalar value
     */
    public function __construct($entityFQN, $uniqueFields, EntityManager $em, EntityFactory $entityFactory)
    {
        $this->fqn = $entityFQN;
        $this->em = $em;
        $this->repository = $this->em->getRepository($this->fqn);

        if (is_string($uniqueFields)) {
            $uniqueFields = array($uniqueFields);
        }

        $this->uniqueFields = $uniqueFields;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Inserts / Updates an Entity with the given fields in the repository and db
     *
     * It uses the uniqueFields from the constructor to find an already persisted entity
     * (first level cache is a key-value store in memory)
     * previous synced entities will be returned or already persisted entities will be loaded
     * the loaded / already synced entities will be updated with the other fields provided
     * in any case after syncing with a new $fields object the $fields are applied to the returned entity
     *
     * @return Object<$entityFQN>
     */
    public function sync(stdClass $fields)
    {
        $hash = $this->hashEntityFields($fields);

        if (array_key_exists($hash, $this->cache)) {
            $entity = $this->cache[$hash];

            if ($this->em->getUnitOfWork()->getEntityState($entity) === UnitOfWork::STATE_DETACHED) {
                // refresh
                $this->cache[$hash] = $entity = $this->loadEntity($fields);
            }

            $this->updateEntity($entity, $fields);
        } else {
            if ($entity = $this->loadEntity($fields)) {
                $this->updateEntity($entity, $fields);
            } else {
                $entity = $this->cache[$hash] = $this->insertEntity($fields);
            }
        }
        $this->em->persist($entity);

        return $entity;
    }

    protected function loadEntity(stdClass $fields)
    {
        $criterias = array();
        foreach ($this->uniqueFields as $field) {
            $criterias[$field] = $fields->$field;
        }

        return $this->repository->findOneBy($criterias);
    }

    protected function updateEntity($entity, stdClass $fields)
    {
        foreach ($fields as $property => $value) {
            if (!in_array($property, $this->uniqueFields)) {
                $setter = 'set'.ucfirst($property);
                $entity->$setter($value);
            }
        }
    }

    protected function insertEntity(stdClass $fields)
    {
        return $this->entityFactory->create($fields);
    }

    protected function hashEntityFields(stdClass $fields)
    {
        $values = array();
        foreach ($this->uniqueFields as $field) {
            $values[] = $fields->$field;
        }
        return implode(':', $values);
    }
}
