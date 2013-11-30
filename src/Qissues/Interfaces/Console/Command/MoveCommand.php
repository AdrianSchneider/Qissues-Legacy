<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\IssueTransition;
use Qissues\Domain\Service\TransitionIssue;
use Qissues\Domain\Shared\Status;
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
                new InputOption('strategy', null, InputOption::VALUE_OPTIONAL, 'Specify an input strategy'),
                new InputOption('data', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Specify fields manually')
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

        if (!$status = $input->getArgument('status') ?: $input->getOption('status')) {
            $output->writeln("<error>Please specify a status</error>");
            return 1;
        }


        try {
            $command = $this;
            $workflow = $tracker->getWorkflow();

            $transitionIssue = new TransitionIssue(
                $tracker->getWorkflow(),
                $tracker->getRepository()
            );

            $transitionIssue(new IssueTransition(
                $number,
                new Status($status),
                function($requirements) use ($command, $input, $output) {
                    $strategy = $command->getStrategy($input);
                    $strategy->init($input, $output, $command->getApplication());
                    return $strategy->create($requirements);
                },
                $this->getComment($input, $output)
            ));

            $output->writeln("Issue <info>#$number</info> is now $status");
            return 0;

        } catch (UnsupportedTransitionException $e) {
            $output->writeln("<error>Cannot transition #$number to $status at this time</error>");
            return 1;
        }
    }

    protected function getStrategy(InputInterface $input)
    {
        return $this->get(sprintf(
            'console.input.details_strategy.%s',
            $input->getOption('strategy') 
                ?: $input->getOption('data') ? 'option' 
                : $this->getParameter('console.input.default_strategy')

        ));
    }
}
