<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class DeleteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delete')
            ->setDescription('Open or re-open an issue')
            ->addArgument('issue', InputArgument::REQUIRED, 'The issue ID')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force deletion.', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector('BitBucket');
        if (!$issue = $connector->find($input->getArgument('issue'))) {
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
