<?php

namespace Qissues\System\Shell;

interface Shell
{
    /**
     * Run a shell command
     * @param string $command
     * return string response
     */
    function run($command);

    /**
     * Escape an argument
     * @param mixed argument
     */
    function escape($argument);
}
