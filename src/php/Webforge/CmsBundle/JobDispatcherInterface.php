<?php

namespace Webforge\CmsBundle;


/**
 * A basic dispatcher for simple background processes
 *
 * @package Webforge\CmsBundle
 */
interface JobDispatcherInterface
{
    public function dispatchCliCommand($commandName, array $args): int;
}