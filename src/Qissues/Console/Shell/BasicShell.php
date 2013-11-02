<?php

namespace Qissues\Console\Shell;

class BasicShell implements Shell
{
    /**
     * {@inheritDoc}
     */
    public function run($command)
    {
        return exec($command);
    }
}
