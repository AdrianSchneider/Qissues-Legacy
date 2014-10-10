<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\IssueChanges;
use Qissues\Domain\Service\EditIssue;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class EditCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('edit')
            ->setDescription('Edit an existing issue')
            ->setDefinition(array(
                new InputArgument('issue', InputArgument::OPTIONAL, 'The Issue ID'),
                new InputOption('strategy', null, InputOption::VALUE_OPTIONAL, 'Specify an input strategy'),
                new InputOption('data', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Specify fields manually'),
                new InputOption('dry-run', 'D', InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE, 'Dump the text out'),
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker();
        $repository = $tracker->getRepository();

        if (!$num = $this->get('console.input.git_id')->getId($input)) {
            $output->writeln('<error>No issue specified</error>');
            return 1;
        }
        if (!$issue = $repository->lookup($number = new Number($num))) {
            $output->writeln('<error>Issue not found.</error>');
            return 1;
        }

        if (!$strategy = $this->getIssueStrategy($input, $output)) {
            $output->writeln("<error>Invalid issue modification strategy specified</error>");
            return 1;
        }

        if (!$changes = $strategy->updateExisting($tracker, $issue)) {
            $output->writeln("<error>Aborted edit</error>");
            return 1;
        }

        $editIssue = new EditIssue($repository);
        $editIssue(new IssueChanges($number, $changes));

        $output->writeln("Issue <info>#$number</info> has been updated");
    }
}
