<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class CommentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('comment')
            ->setDescription('Comment on an issue')
            ->addArgument('issue', InputArgument::REQUIRED, 'The issue ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($input->getArgument('issue'))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $message = $this->getMessage();
        $connector->comment($issue, $message);

        $output->writeln("Left a comment on #$issue[id]");
    }

    protected function getMessage()
    {
        $filename = tempnam('.', 'qissues');
        $editor = getenv('EDITOR') ?: 'vim';
        exec("$editor $filename > `tty`");
        $data = file_get_contents($filename);
        unlink($filename);

        return $data;
    }
}
