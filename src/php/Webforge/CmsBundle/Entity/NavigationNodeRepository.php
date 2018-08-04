<?php

namespace Webforge\CmsBundle\Entity;

class NavigationNodeRepository extends \Doctrine\ORM\EntityRepository
{
    public function nodesQueryBuilder($node = null)
    {
        $qb = $this->createQueryBuilder('node');
    
        if ($node) {
            $qb
      ->where($qb->expr()->lt('node.rgt', $node->getRgt()))
        ->andWhere($qb->expr()->gt('node.lft', $node->getLft()))
      ;
    
            /*
            $rootId = $node->getRoot();
            $qb->andWhere(
              $rootId === NULL
                ? $qb->expr()->isNull('node.root')
                : $qb->expr()->eq('node.root', is_string($rootId) ? $qb->expr()->literal($rootId) : $rootId)
            );
            */
        }
    
        $qb->orderBy('node.lft', 'ASC');

        return $qb;
    }

    public function getRootNode()
    {
        $qb = $this->nodesQueryBuilder();
        $qb->andWhere($qb->expr()->eq('node.lft', 1));

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Returns all Nodes (not filtered in any way)
     *
     * @return array
     */
    public function findAllNodes()
    {
        $qb = $this->createQueryBuilder('node');
        /*
        $qb->addSelect('page');
        $qb->leftJoin('node.page', 'page');
        */
    
        $query = $qb->getQuery();
    
        return $query->getResult();
    }
}
