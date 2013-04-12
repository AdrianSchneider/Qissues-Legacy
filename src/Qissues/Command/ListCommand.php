<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AddLibraryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('list')
            ->setDescription('List all issues, optionally filtering them.')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
