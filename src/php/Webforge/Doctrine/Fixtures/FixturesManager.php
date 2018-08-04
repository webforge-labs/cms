<?php

namespace Webforge\Doctrine\Fixtures;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\FixtureInterface as DCFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManager;
use RuntimeException;

class FixturesManager
{
    protected $em;
    protected $executor;
    protected $loader;
    protected $purger;

    protected $log;
  
    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        if (!class_exists('Doctrine\Common\DataFixtures\Executor\ORMExecutor', $autoload=true)) {
            throw new RuntimeException('To use the fixtures manager you need to install doctrine/data-fixtures: '."\ncomposer require --dev doctrine/data-fixtures:1.0.*@dev");
        }
    }
  
    public function add(DCFixture $fixture)
    {
        $this->getLoader()->addFixture($fixture);
        return $this;
    }
  
    public function execute()
    {
        return $this->getExecutor()->execute($this->getLoader()->getFixtures());
    }

    public function flush()
    {
        $this->em->flush();
    }

    public function getLoader()
    {
        if (!isset($this->loader)) {
            $this->loader = new Loader();
        }
        return $this->loader;
    }
  
    public function getExecutor()
    {
        if (!isset($this->executor)) {
            $this->executor = new ORMExecutor($this->em, $this->getPurger());
            $that = $this;
            $this->executor->setLogger(function ($msg) use ($that) {
                $that->appendLog($msg);
            });
        }
    
        return $this->executor;
    }

    public function getPurger()
    {
        if (!isset($this->purger)) {
            $this->purger = new QNDTruncateORMPurger($this->em);
            $this->purger->setPurgeMode(QNDTruncateORMPurger::PURGE_MODE_TRUNCATE);
        }
        return $this->purger;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function appendLog($msg)
    {
        $this->log .= $msg;
        return $this;
    }

    public function resetFixtures()
    {
        unset($this->executor); // to reset the reference repository as well
        unset($this->loader);
    }
}
