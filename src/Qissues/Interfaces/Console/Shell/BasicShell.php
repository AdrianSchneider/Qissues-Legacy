<?php

namespace Qissues\Interfaces\Console\Shell;

class BasicShell implements Shell
{
    /**
     * {@inheritDoc}
     */
    public function run($command)
    {
        return exec($command);
    }

    /**
     * {@inheritDoc}
     */
    public function escape($argument)
    {
        return escapeshellarg($argument);
    }
}
