<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CloseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('close')
            ->setDescription('Close or re-open an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $message = $this->getComment();
        // TODO Jira
        $connector->changeStatus($issue, 'resolved');

        if ($message) {
            $connector->comment($issue, $message);
        }

        $output->writeln("Issue <info>#$issue[id]</info> has been closed.");
    }

    protected function getComment()
    {
        $filename = tempnam('.', 'qissues');
        $editor = getenv('EDITOR') ?: 'vim';
        exec("$editor $filename > `tty`");
        $data = file_get_contents($filename);
        unlink($filename);

        return $data;
    }
}
