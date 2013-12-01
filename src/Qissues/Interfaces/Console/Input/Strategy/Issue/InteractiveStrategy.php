<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Application\Tracker\IssueTracker;
use Qissues\Application\Tracker\FieldMapping;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InteractiveStrategy implements IssueStrategy
{
    protected $input;
    protected $output;
    protected $dialog;

    /**
     * Brings interaction functionality into scope
     * {@inheritDoc}
     */
    public function init(InputInterface $input, OutputInterface $output, Application $console)
    {
        if ($this->input) {
            throw new \BadMethodCallException('Can only init once');
        }
        if (!$input->isInteractive()) {
            throw new \RunTimeException('Input is not interactive');
        }

        $this->input = $input;
        $this->output = $output;
        $this->dialog = $console->getHelperSet()->get('dialog');
    }

    /**
     * {@inheritDoc}
     */
    public function createNew(IssueTracker $tracker)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->buildData($mapping));
    }

    /**
     * {@inheritDoc}
     */
    public function updateExisting(IssueTracker $tracker, Issue $existing)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->buildData($mapping, $existing));
    }

    /**
     * Build the data by asking the user interactively
     * @param FieldMapping mapping
     * @param Issue|null
     */
    protected function buildData(FieldMapping $mapping, Issue $issue = null)
    {
        $data = array();
        foreach ($mapping->getEditFields($issue) as $key => $value) {
            $data[$key] = $this->ask($key, $value, $issue);
        }

        return $data;
    }

    /**
     * Simpler ask method
     * @param string $question to ask
     * @param string $default value
     * @return string answer
     */
    protected function ask($question, $default, $previous = null)
    {
        if ($previous) {
            // TODO not pretty
            $this->output->writeln("<info>Old Value</info>: $default <comment>leave blank to keep</comment>");
        }
        return $this->dialog->ask($this->output, $question . ': ', $default);
    }
}
