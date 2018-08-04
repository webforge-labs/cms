<?php

namespace Webforge\CmsBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * A basic dispatcher for simple background processes
 *
 * @package Webforge\CmsBundle
 */
class JobDispatcher
{
    /**
     * @var string absolute path to the cli command (accepts cli command name and symfony command line arguments)
     */
    protected $cli;

    /**
     * @var string production|test|staging etc|
     */
    protected $symfonyEnv;

    /**
     * @var array
     */
    protected $env;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct($cli, $symfonyEnv, LoggerInterface $logger, array $env)
    {
        $this->cli = $cli ?: '/app/bin/cli.sh';
        $this->symfonyEnv = $symfonyEnv;
        $this->env = $env;
        $this->logger = $logger;
    }

    public function dispatchCliCommand($commandName, array $args): int
    {
        $cliCommand = sprintf(
            '%s %s --env=%s -v %s',
            $this->cli,
            $commandName,
            $this->symfonyEnv,
            $cliArgs = implode(' ', $args)
        );

        $this->logger->info('Dispatch CLI job: '.$this->cli.' '.$commandName.' '.$cliArgs);

        $command = sprintf('{ %s 2>&1 & }; pid=$!; echo $pid;', $cliCommand);
        $process = new Process($command, null, $this->env);

        $process->mustRun();

        // get the background pid, not the foreground pit
        $pid = (int)$process->getOutput();

        if ($pid <= 0) {
            throw new \RuntimeException('Cannot start background process. An unusual pid is returned: '.$process->getOutput().' '.$process->getErrorOutput());
        }

        $this->logger->debug('dispatched with pid: '.$pid);

        return $pid;
    }
}
