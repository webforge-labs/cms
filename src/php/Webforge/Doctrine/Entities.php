<?php

namespace Webforge\Doctrine;

use Webforge\Common\ClassUtil;
use Doctrine\ORM\EntityManager;
use Webforge\Doctrine\Exceptions\EntityNotFoundException;
use Webforge\Doctrine\CollectionSynchronizer;

class Entities
{
    protected $em;

    protected $entitiesNamespace;

    public function __construct(EntityManager $em, $entitiesNamespace)
    {
        $this->em = $em;
        $this->entitiesNamespace = $entitiesNamespace;
    }

    /**
     * Returns ONE Entity found by given criterias
     * @param  string $entityName Just the name of the entity with relative Namespace (ucfirst)
     * @param  array|integer $criterias $field=>$value or just integer for 'id'=>$integer
     * @return the instance from the entity
     */
    public function hydrate($entityName, $criterias)
    {
        if (is_integer($criterias)) {
            $criterias = array('id'=>$criterias);
        }

        $entityFQN = $this->expandEntityName($entityName);
        $entity = $this->em->getRepository($entityFQN)->findOneBy($criterias);

        if (!($entity instanceof $entityFQN)) {
            throw new EntityNotFoundException('Entity '.$entityFQN.' not found with criterias: '.json_encode($criterias));
        }

        return $entity;
    }

    public function findOneBy($entityName, $criterias)
    {
        return $this->getRepository($entityName)->findOneBy($criterias);
    }

    public function persist($entity)
    {
        $this->em->persist($entity);
    }

    public function flush()
    {
        $this->em->flush();
    }

    public function clear()
    {
        $this->em->clear();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
    }

    public function getRepository($entityName)
    {
        $entityFQN = $this->expandEntityName($entityName);
        return $this->em->getRepository($entityFQN);
    }

    public function expandEntityName($entityName)
    {
        return ClassUtil::setNamespace($entityName, $this->entitiesNamespace);
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function getCollectionSynchronizer($entityName, $relationProperty, array $options = array())
    {
        $synchronizer = CollectionSynchronizer::createFor($this->expandEntityName($entityName), $relationProperty, $this->em, $options);

        return $synchronizer;
    }
}
