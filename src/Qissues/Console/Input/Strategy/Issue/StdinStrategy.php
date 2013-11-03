<?php

namespace Qissues\Console\Input\Strategy\Issue;

use Qissues\Model\Issue;
use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\FileFormats\FileFormat;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StdinStrategy
{
    protected $inputStream;
    protected $fileFormat;

    public function __construct($inputStream = 'php://stdin', FileFormat $fileFormat)
    {
        $this->inputStream = $inputStream;
        $this->fileFormat = $fileFormat;
    }

    /**
     * ignored
     * {@inheritDoc}
     */
    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * Uses a custom-formatted input stream to create issues
     *
     * {@inheritDoc}
     */
    public function createNew(IssueTracker $tracker)
    {
        $mapping = $tracker->getMapping();
        return $mapping->toNewIssue($this->fileFormat->parse(
            trim(file_get_contents($this->inputStream))
        ));
    }

    /**
     * Same as createNew
     *
     * {@inheritDoc}
     */
    public function updateExisting(IssueTracker $tracker, Issue $exiting)
    {
        return $this->createNew($tracker);
    }
}
