<?php

namespace Qissues\Console\Command;

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
        $repository = $tracker->getRepository();
        $issueFactory = $this->get('console.input.external_issue_factory');

        $number = $repository->persist($issueFactory->createForTracker($tracker));
        $output->writeln("Issue <info>#$number</info> has been created");
    }
}
