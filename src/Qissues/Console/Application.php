<?php

namespace Qissues\Console;

use Qissues\Command;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Finder\Finder;

class Application extends BaseApplication
{
    /**
     * {@inheritDoc}
     */
    protected function registerCommands()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->name('*Command.php')
            ->notName('Command.php')
            ->in(__DIR__.'/../Command');
        
        foreach ($finder as $file) {
            $class = "Qissues\\Command\\" . basename($file, ".php");
            $this->add(new $class());
        }
    }
}
