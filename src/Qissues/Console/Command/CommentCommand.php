<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CommentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('comment')
            ->setDescription('Comment on an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Specify message', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $message = $input->getOption('message') ?: $this->getMessage();
        $connector->comment($issue, $message);

        if ($message) {
            $output->writeln("Left a comment on #$issue[id]");
        } else {
            $output->writeln("<error>No message left</error>");
        }
    }

    protected function getMessage()
    {
        $filename = tempnam('.', 'qissues');
        $editor = getenv('EDITOR') ?: 'vim';
        exec("$editor $filename > `tty`");
        $data = file_get_contents($filename);
        unlink($filename);

        return trim($data);
    }
}
