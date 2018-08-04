<?php

namespace Webforge\Doctrine\Console;

use Webforge\Doctrine\Container as DoctrineContainer;
use Symfony\Component\Console\Input\InputOption;
use Webforge\Common\System\System;

abstract class AbstractDoctrineCommand extends \Webforge\Console\Command\CommandAdapter
{

  /**
   * Webforge\Doctrine\Container
   */
    protected $dcc;

    public function __construct(DoctrineContainer $dcc, System $system)
    {
        $this->dcc = $dcc;
        parent::__construct($this->name, $system);
    }

    protected function configure()
    {
        $this->addOption(
      'con',
        '',
        InputOption::VALUE_REQUIRED,
      'Shortname of the connection (configuration)',
      'default'
    );
    
        $this->addOption(
      'dry-run',
        '',
        InputOption::VALUE_NONE,
      'When set not database actions are processed, only output is given'
    );
    }

    protected function getEntityManager($con)
    {
        return $this->dcc->getEntityManager($con);
    }
}
