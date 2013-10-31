<?php

namespace Qissues\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AssignCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('assign')
            ->setDescription('(Re-)assign an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
            ->addArgument('assignee', InputArgument::OPTIONAL, 'New assignee', null)
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Specify message', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('work in progress');
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        if (!$assignee = $input->getArgument('assignee')) {
            return $output->writeln("<error>No assignee</error>");
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
