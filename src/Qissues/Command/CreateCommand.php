<?php

namespace Qissues\Command;

use Qissues\Connector\Connector;
use Qissues\Input\TemplatedInput;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new issue')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker();
        $issueFactory = new ExternalIssueFactory();

        $number = $tracker->persist($issueFactory->createForTracker($tracker));
        $output->writeln("Issue <info>#$number</info> has been created");
    }
}
