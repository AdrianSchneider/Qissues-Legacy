<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\IssuePlan;
use Qissues\Domain\Service\PlanIssue;
use Qissues\Domain\Shared\Milestone;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PlanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('plan')
            ->setDescription('Plan an issue for a milestone')
            ->setDefinition(array(
                new InputArgument('issue', InputArgument::OPTIONAL, 'The Issue ID'),
                new InputArgument('milestone', InputArgument::OPTIONAL, 'The milestone', null),
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

        if ($input->getArgument('milestone')) {
            $milestone = new Milestone($input->getArgument('milestone'));
        } else {
            $output->writeln("<error>No milestone</error>");
            return 1;
        }

        $planIssue = new PlanIssue($repository);
        $planIssue(new IssuePlan($number, $milestone));

        $output->writeln("Issue <info>#$number</info> has been planned for <info>$milestone</info>");
    }
}
