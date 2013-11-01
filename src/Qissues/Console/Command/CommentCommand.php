<?php

namespace Qissues\Console\Command;

use Qissues\Model\Number;
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

        if (!$strategy = $this->getStrategy($input)) {
            $output->writeln("<error>Invalid commenting strategy specified</error>");
            return 1;
        }

        $strategy->init($input, $output, $this->getApplication());
        if (!$comment = $strategy->createNew($tracker)) {
            $output->writeln("<error>No comment left</error>");
            return 1;
        }

        $repository->comment($number, $comment);
        $output->writeln("Left a comment on #$number");
    }

    protected function getStrategy(InputInterFace $input)
    {
        $selected = $input->getOption('message') ? 'option' : ($input->getOption('strategy') ?: $this->getParameter('console.input.default_strategy'));
        $strategy = sprintf('console.input.comment_strategy.%s', $selected);

        if (!$this->getApplication()->getContainer()->has($strategy)) {
            return;
        }

        return $this->get($strategy);
    }
}
