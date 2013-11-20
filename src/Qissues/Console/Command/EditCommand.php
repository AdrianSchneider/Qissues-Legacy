<?php

namespace Qissues\Console\Command;

use Qissues\Model\Number;
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

        if (!$strategy = $this->getIssueStrategy($input, $output)) {
            $output->writeln("<error>Invalid issue modification strategy specified</error>");
            return 1;
        }

        if (!$changes = $strategy->updateExisting($tracker, $issue)) {
            $output->writeln("<error>Aborted edit</error>");
            return 1;
        }

        $repository->update($changes, $number);
        $output->writeln("Issue <info>#$number</info> has been updated");
    }
}
