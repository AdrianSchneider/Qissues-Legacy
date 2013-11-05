<?php

namespace Qissues\Console\Input;

use Symfony\Component\Console\Input\InputInterface;

class ReportManager
{
    protected $reports;

    public function __construct(array $reports)
    {
        $this->reports = $reports;
    }

    public function findReport(InputInterface $input)
    {
        if ($name = $input->getOption('report')) {
            if (!isset($this->reports[$name])) {
                throw new Exception('Could not find report');
            }

            return $this->reports[$name];
        }

        foreach (array('keyword', 'status', 'assignee', 'priority', 'type', 'mine', 'ids') as $field) {
            if ($input->getOption($field)) {
                return;
            }
        }

        if (isset($this->reports['default'])) {
            return $this->reports['default'];
        }
    }
}