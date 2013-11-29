<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CommentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('comment')
            ->setDescription('Comment on an issue')
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

        if (!$strategy = $this->getCommentStrategy($input, $output)) {
            $output->writeln("<error>Invalid commenting strategy specified</error>");
            return 1;
        }

        if (!$comment = $strategy->createNew($tracker)) {
            $output->writeln("<error>No comment left</error>");
            return 1;
        }

        $repository->comment($number, $comment);
        $output->writeln("Left a comment on <info>#$number</info>");
    }
}
