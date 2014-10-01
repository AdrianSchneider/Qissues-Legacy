<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\IssuePlan;
use Qissues\Domain\Service\PlanIssue;
use Qissues\Domain\Shared\NotPlanned;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeferCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('defer')
            ->setDescription('Defer an issue')
            ->setDefinition(array(
                new InputArgument('issue', InputArgument::OPTIONAL, 'The Issue ID')
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

        $planIssue = new PlanIssue($repository);
        $planIssue(new IssuePlan($number, new NotPlanned()));

        $output->writeln("Issue <info>#$number</info> has been defered");
    }
}
