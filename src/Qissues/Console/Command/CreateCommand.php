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
            ->setDefinition(array(
                new InputOption('strategy', null, InputOption::VALUE_OPTIONAL, 'Specify an input strategy'),
                new InputOption('data', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Specify fields manually')
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker();
        $repository = $tracker->getRepository();

        if (!$strategy = $this->getIssueStrategy($input, $output)) {
            $output->writeln("<error>Invalid issue creation strategy specified</error>");
            return 1;
        }

        if (!$issue = $strategy->createNew($tracker)) {
            $output->writeln("<error>Issue aborted</error>");
            return 1;
        }

        $number = $repository->persist($issue);
        $output->writeln("Issue <info>#$number</info> has been created");
    }
}
