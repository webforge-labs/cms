<?php

namespace Webforge\Doctrine\Fixtures;

use Doctrine\ORM\EntityManager;

class QNDTruncateORMPurger extends \Doctrine\Common\DataFixtures\Purger\ORMPurger
{
    private $em;
  
    public function __construct(EntityManager $em = null)
    {
        $this->em = $em;
        parent::__construct($em);
    }
  
    public function purge()
    {
        if ($this->getPurgeMode() === self::PURGE_MODE_TRUNCATE) {
            // QND Hack: if you got better plans for this: let me now!
            $this->em->getConnection()->executeQuery('set foreign_key_checks = 0');
            parent::purge();
            $this->em->getConnection()->executeQuery('set foreign_key_checks = 1');
        } else {
            parent::purge();
        }
    }
}
