<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Application\Tracker\IssueTracker;
use Qissues\Interfaces\Console\Input\ExternalFileEditor;
use Qissues\Interfaces\Console\Input\FileFormats\FileFormat;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditStrategy implements IssueStrategy
{
    protected $editor;
    protected $fileFormat;

    public function __construct(ExternalFileEditor $editor, FileFormat $fileFormat)
    {
        $this->editor = $editor;
        $this->fileFormat = $fileFormat;
    }

    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * {@inheritDoc}
     */
    public function createNew(IssueTracker $tracker)
    {
        return $this->getNewIssue($tracker);
    }

    /**
     * {@inheritDoc}
     */
    public function updateExisting(IssueTracker $tracker, Issue $existing)
    {
        return $this->getNewIssue($tracker, $existing);
    }

    /**
     * Creates a NewIssue from an edited file populated by the file format
     *
     * @param IssueTracker $tracker
     * @param Issue|null $existing
     * @return NewIssue
     */
    protected function getNewIssue(IssueTracker $tracker, Issue $existing = null)
    {
        $mapping = $tracker->getMapping();
        $details = $this->fileFormat->seed($mapping->getExpectedDetails($existing));

        if (!$content = trim($this->editor->getEdited($details))) {
            return;
        }

        return $mapping->toNewIssue(
            $this->fileFormat->parse($content)->getDetails()
        );
    }
}
