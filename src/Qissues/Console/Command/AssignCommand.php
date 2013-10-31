<?php

namespace Qissues\Console\Command;

use Qissues\Model\Number;
use Qissues\Model\Meta\User;
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
        $tracker = $this->getApplication()->getTracker();
        $repository = $tracker->getRepository();

        $number = new Number($this->get('console.input.git_id')->getId($input));
        if (!$issue = $repository->lookup($number)) {
            $output->writeln('<error>Issue not found.</error>');
            return 1;
        }

        if (!$assignee = $input->getArgument('assignee')) {
            $output->writeln("<error>No assignee</error>");
            return 1;
        }

        if ($message = $input->getOption('message')) {
            throw new \Exception('work in progress');
        }

        $repository->assign($number, new User($assignee));
        $output->writeln("Issue <info>#$issue[id]</info> has been assigned to <info>$assignee</info>");
    }
}
