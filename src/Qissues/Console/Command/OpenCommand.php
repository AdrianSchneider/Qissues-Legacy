<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class OpenCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('open')
            ->setDescription('Open or re-open an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('work in progress');
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $message = $this->getComment();
        $connector->changeStatus($issue, 'open');

        if ($message) {
            $connector->comment($issue, $message);
        }

        $output->writeln("Issue <info>#$issue[id]</info> has been (re-)opened.");
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
