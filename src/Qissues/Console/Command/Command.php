<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Command extends BaseCommand
{
    public function get($service)
    {
        return $this->getApplication()->getContainer()->get($service);
    }

    public function getParameter($parameter)
    {
        return $this->getApplication()->getContainer()->getParameter($service);
    }
}
