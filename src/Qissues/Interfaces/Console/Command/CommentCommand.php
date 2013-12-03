<?php

namespace Qissues\Interfaces\Console\Command;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\NewComment;
use Qissues\Domain\Service\CommentOnIssue;
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

        $commentOnIssue = new CommentOnIssue($repository);
        $commentOnIssue(new NewComment($number, $comment));

        $output->writeln("Left a comment on <info>#$number</info>");
    }

    /**
     * Retrieves a CommentStrategy ready for use
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return CommentStrategy
     */
    protected function getCommentStrategy(InputInterface $input, OutputInterface $output)
    {
        $selected = $this->getCommentStrategyType($input);
        $strategy = sprintf('console.input.comment_strategy.%s', $selected);

        if (!$this->getApplication()->getContainer()->has($strategy)) {
            throw new \BadMethodCallException("Could not find strategy $strategy");
        }

        $strategy = $this->get($strategy);
        $strategy->init($input, $output, $this->getApplication());

        return $strategy;
    }

    /**
     * Determine the strategy to use
     * @param InputInterface $input
     * @return string strategy suffix
     */
    protected function getCommentStrategyType(InputInterface $input)
    {
        if ($input->getOption('message') !== null) {
            return 'option';
        }
        if ($strategy = $input->getOption('strategy')) {
            return $strategy;
        }

        return $this->getParameter('console.input.default_strategy');
    }
}
