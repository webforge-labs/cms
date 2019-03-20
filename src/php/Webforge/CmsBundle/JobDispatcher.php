<?php

namespace Webforge\CmsBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * A basic dispatcher for simple background processes
 *
 * @package Webforge\CmsBundle
 */
class JobDispatcher implements JobDispatcherInterface
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

    /**
     * @var string
     */
    protected $logfile;

    public function __construct($cli, $symfonyEnv, LoggerInterface $logger, array $env, ParameterBagInterface $bag)
    {
        $this->cli = $cli ?: '/app/bin/cli.sh';
        $this->logs = $bag->get('kernel.logs_dir');
        $this->logfile = $this->logs.'/jobs.log';
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

        $this->logger->info('Dispatch CLI job: '.$cliCommand.' with env: '.json_encode($this->env));

        $command = sprintf('{ %s >> %s 2>&1 & }; echo "$!"; ', $cliCommand, $this->logfile);
        $process = Process::fromShellCommandline($command, null, $this->env);

        $process->mustRun();

        $this->logger->debug('stdout: '.$process->getOutput().' stderr: '.$process->getErrorOutput());

        // get the background pid, not the foreground pid
        $pid = (int) $process->getOutput();

        if ($pid <= 0) {
            throw new \RuntimeException('Cannot start background process for job: '.$cliCommand.' . An unusual pid is returned: '.$process->getOutput().' '.$process->getErrorOutput());
        }

        if (!empty($process->getErrorOutput())) { // this can only be error output directly from the dispatch, not from the command itself (which is written to the file)
            // this can happen for example, if the logfile cannot be written
            throw new \RuntimeException('Cannot start background process for job: '.$cliCommand.' . Stderr is not empty: '.$process->getErrorOutput().' stdout: '.$process->getOutput());
        }

        $this->logger->debug('dispatched with pid: '.$pid);

        return $pid;
    }
}
