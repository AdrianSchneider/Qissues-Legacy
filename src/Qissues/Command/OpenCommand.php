<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class OpenCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('open')
            ->setDescription('Open or re-open an issue')
            ->addArgument('issue', InputArgument::REQUIRED, 'The issue ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector('BitBucket');
        if (!$issue = $connector->find($input->getArgument('issue'))) {
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
        exec("vim $filename > `tty`");
        $data = file_get_contents($filename);
        unlink($filename);

        return $data;
    }
}
