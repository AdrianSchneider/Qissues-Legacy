<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Application\Tracker\IssueTracker;
use Qissues\Application\Tracker\FieldMapping;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptionStrategy implements IssueStrategy
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * Bring input into scope
     *
     * {@inheritDoc}
     */
    public function init(InputInterface $input, OutputInterface $output, Application $console)
    {
        $this->input = $input;
    }

    /**
     * Create's a NewIssue from input options
     *
     * {@inheritDoc}
     */
    public function createNew(IssueTracker $tracker)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->buildData($mapping->getExpectedDetails()));
    }

    /**
     * Create's a NewIssue from input options, modifying an existing Issue
     *
     * {@inheritDoc}
     */
    public function updateExisting(IssueTracker $tracker, Issue $existing)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->buildData($mapping->getExpectedDetails($existing)));
    }

    /**
     * Constructs the array of data
     * 
     * @param ExpectedDetails $expectations
     * @return array
     */
    protected function buildData(ExpectedDetails $expectations)
    {
        $out = $expectations->getDefaults();
        foreach ($this->input->getOption('data') as $option) {
            list($key, $value) = explode('=', $option, 2);
            $out[$key] = $value;
        }

        return $out;
    }
}
