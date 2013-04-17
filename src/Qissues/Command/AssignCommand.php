<?php

namespace Qissues\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class AssignCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('assign')
            ->setDescription('(Re-)assign an issue')
            ->addArgument('issue', InputArgument::REQUIRED, 'The issue ID')
            ->addArgument('assignee', InputArgument::REQUIRED, 'New assignee')
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Specify message', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($input->getArgument('issue'))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $message = trim($input->getOption('message') ?: $this->getComment());
        $connector->assign($issue, $assignee = $input->getArgument('assignee'));

        if ($message) {
            $connector->comment($issue, $message);
        }

        $output->writeln("Issue <info>#$issue[id]</info> has been assigned to <info>$assignee</info>");
    }

    protected function getComment()
    {
        $default = 'Leave a comment?';
        $filename = tempnam('.', 'qissues');
        file_put_contents($filename, $default);
        $editor = getenv('EDITOR') ?: 'vim';
        exec("$editor $filename > `tty`");
        $data = trim(file_get_contents($filename));
        unlink($filename);

        if ($data == $default) {
            $data = '';
        }


        return $data;
    }
}
