<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeleteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delete')
            ->setDescription('Open or re-open an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force deletion.', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('work in progress');
        $connector = $this->getApplication()->getConnector('BitBucket');
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        if (!$input->getOption('force')) {
            $output->writeln("<error>This operation is destructive.</error> Use the --force option to confirm.");
            return;
        }

        $connector->delete($issue, 'open');
        $output->writeln("Issue <info>#$issue[id]</info> has been deleted.");
    }
}
