<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\IssueAssignment;
use Qissues\Domain\Service\AssignIssue;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
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
            ->setDefinition(array(
                new InputArgument('issue', InputArgument::OPTIONAL, 'The Issue ID'),
                new InputArgument('assignee', InputArgument::OPTIONAL, 'The assignee', null),
                new InputOption('me', null, InputOption::VALUE_NONE, 'Assign to me', null),
                new InputOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Specify message', null),
                new InputOption('comment-strategy', null, InputOption::VALUE_OPTIONAL, 'Specify an input strategy')
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

        if ($input->getOption('me')) {
            $assignee = new CurrentUser();
        } elseif ($account = $input->getArgument('assignee')) {
            $assignee = new User($account);
        } else {
            $output->writeln("<error>No assignee</error>");
            return 1;
        }

        $assignIssue = new AssignIssue($repository);
        $assignIssue(new IssueAssignment(
            $number,
            $assignee,
            $this->getOptionalComment($input, $output)
        ));

        $output->writeln("Issue <info>#$number</info> has been assigned to <info>$assignee</info>");
    }
}
