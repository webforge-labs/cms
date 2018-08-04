<?php

namespace Webforge\Doctrine\Console;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Webforge\Doctrine\Container as DoctrineContainer;
use Webforge\Common\System\System;

class ConsoleBridge
{

  /**
   * Webforge\Doctrine\Container
   */
    protected $dcc;

    protected $system;

    public function __construct(DoctrineContainer $dcc, System $system)
    {
        $this->dcc = $dcc;
        $this->system = $system;
    }

    public function augment($application)
    {
        $application->getHelperSet()->set(
      new EntityManagerHelper($this->dcc->getEntityManager()),
      'em'
    );

        $application->addCommands(array(
      new ValidateSchemaCommand(),
      new ORMUpdateSchemaCommand($this->dcc, $this->system)
    ));
    }
}
