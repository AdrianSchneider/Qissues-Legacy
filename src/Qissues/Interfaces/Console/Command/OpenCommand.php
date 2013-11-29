<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\OpenStatus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class OpenCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('open')
            ->setDescription('Open or re-open an issue')
            ->setDefinition(array(
                new InputArgument('issue', InputArgument::OPTIONAL, 'The Issue ID'),
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

        if ($comment = $this->getComment($input, $output)) {
            $repository->comment($number, $comment);
        }

        $repository->changeStatus($number, new OpenStatus());
        $output->writeln("Issue <info>#$number</info> has been opened");
    }
}
