<?php

namespace Qissues\Interfaces\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Command extends BaseCommand
{
    /**
     * Gets a service from the container
     * @param string $service
     * @return mixed
     */
    public function get($service)
    {
        return $this->getApplication()->getContainer()->get($service);
    }

    /**
     * Gets a parameter from the container
     * @param string $paramter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->getApplication()->getContainer()->getParameter($parameter);
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

    /**
     * Retrieves a IssueStrategy ready for use
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return IssueStrategy
     */
    protected function getIssueStrategy(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('strategy')) {
            $strategy = $input->getOption('strategy');
        } elseif ($input->getOption('data')) {
            $strategy = 'option';
    /*
        possible without blocking?

        } elseif (strlen(trim(file_get_contents('php://stdin'))) {
            $strategy = 'stdin';
    */
        } else {
            $strategy = $this->getParameter('console.input.default_strategy');
        }

        $strategy = sprintf('console.input.issue_strategy.%s', $strategy);

        if (!$this->getApplication()->getContainer()->has($strategy)) {
            return;
        }

        $strategy = $this->get($strategy);
        $strategy->init($input, $output, $this->getApplication());

        return $strategy;
    }

    /**
     * Gets a populated NewComment from the selected strategy
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return NewComment|null
     */
    protected function getComment(InputInterface $input, OutputInterface $output)
    {
        $strategy = $this->getCommentStrategy($input, $output);
        return $strategy->createNew($this->getApplication()->getTracker());
    }
}
