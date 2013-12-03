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
     * Get an optional comment from the user
     *
     * @param InputInterface $input
     * @param OutputInerface $output
     * @return Message|null
     */
    protected function getOptionalComment(InputInterface $input, OutputInterface $output)
    {
        if (!$type = $this->getOptionalCommentStrategyType($input)) {
            return;
        }

        $strategyService = sprintf('console.input.comment_strategy.%s', $type);
        if (!$this->getApplication()->getContainer()->has($strategyService)) {
            throw new \BadMethodCallException("Could not find strategy $type");
        }

        $strategy = $this->get($strategyService);
        $strategy->init($input, $output, $this->getApplication());

        return $strategy->createNew($this->getApplication()->getTracker());
    }

    /**
     * Get the optional comment strategy
     * Uses -m value, or explict strategy, otherwise nothing
     * @param InputInterface $input
     * @return string|null strategy
     */
    protected function getOptionalCommentStrategyType(InputInterface $input)
    {
        if ($input->getOption('message') !== null) {
            return 'option';
        }
        if ($strategy = $input->getOption('comment-strategy')) {
            return $strategy;
        }
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
}
