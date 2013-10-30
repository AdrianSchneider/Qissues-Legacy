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

class EditCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('edit')
            ->setDescription('Edit an existing issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker();
        $number = new Number($this->get('console.input.git_id')->getId($input));
        if (!$issue = $tracker->lookup($number)) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $issueFactory = new ExternalIssueFactory(/* ... */);
        $tracker->update($issueFactory->updateForTracker($tracker, $issue));

        $output->writeln("Issue <info>#$issue[id]</info> has been updated");
    }
}
