<?php

namespace Qissues\Console\Shell;

interface Shell
{
    /**
     * Run a shell command
     * @param string $command
     * return string response
     */
    function run($command);
}