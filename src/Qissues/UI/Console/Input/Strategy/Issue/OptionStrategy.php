<?php

namespace Qissues\Console\Input\Strategy\Issue;

use Qissues\Model\Issue;
use Qissues\Model\Tracker\IssueTracker;
use Qissues\Model\Tracker\FieldMapping;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptionStrategy implements IssueStrategy
{
    protected $input;

    public function init(InputInterface $input, OutputInterface $output, Application $console)
    {
        $this->input = $input;
    }

    public function createNew(IssueTracker $tracker)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->buildData());
    }

    public function updateExisting(IssueTracker $tracker, Issue $existing)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->buildData());
    }

    protected function buildData()
    {
        $out = array();
        foreach ($this->input->getOption('data') as $option) {
            list($key, $value) = explode('=', $option, 2);
            $out[$key] = $value;
        }

        return $out;
    }
}
