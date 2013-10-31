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
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
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

        $issueFactory = $this->get('console.input.external_issue_factory');
        $repository->update($issueFactory->updateForTracker($tracker, $issue), $number);

        $output->writeln("Issue <info>#$number</info> has been updated");
    }
}
