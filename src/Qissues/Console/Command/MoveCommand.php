<?php

namespace Qissues\Console\Command;

use Qissues\Model\Number;
use Qissues\Model\Meta\Status;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class MoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('move')
            ->setDescription('Move an issue to a new status')
            ->setDefinition(array(
                new InputArgument('issue', InputArgument::OPTIONAL, 'The Issue ID'),
                new InputArgument('status', InputArgument::OPTIONAL, 'New status'),
                new InputOption('status', 's', InputOption::VALUE_OPTIONAL, 'New status'),
                new InputOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Specify message', null),
                new InputOption('strategy', null, InputOption::VALUE_OPTIONAL, 'Specify an input strategy')
            ))
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

        $comment = $this->getComment($input, $output);

        if (!$status = $input->getArgument('status') ?: $input->getOption('status')) {
            $output->writeln("<error>Please specify a status</error>");
            return 1;
        }

        $repository->changeStatus($number, new Status($status));
        if ($comment) {
            $repository->comment($number, $comment);
        }

        $output->writeln("Issue <info>#$number</info> is now $status");
    }
}
