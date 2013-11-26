<?php

namespace Qissues\Console\Command;

use Qissues\Model\Querying\Number;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeleteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delete')
            ->setDescription('Delete an issue from the tracker')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force deletion.', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            $output->writeln("<error>This operation is destructive.</error> Use the --force option to confirm.");
            return;
        }

        $tracker = $this->getApplication()->getTracker();
        $repository = $tracker->getRepository();

        $number = new Number($this->get('console.input.git_id')->getId($input));
        if (!$issue = $repository->lookup($number)) {
            $output->writeln('<error>Issue not found.</error>');
            return 1;
        }

        $repository->delete($number);
        $output->writeln("Issue <info>#$number</info> has been deleted.");
    }
}
