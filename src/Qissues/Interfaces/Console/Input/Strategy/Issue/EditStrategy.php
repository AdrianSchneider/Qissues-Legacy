<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
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
    protected $output;
    protected $timeout;

    /**
     * @param ExternalFileEditor $editor
     * @param FileFormat $fileFormat
     * @param integer $timeout for showing errors
     */
    public function __construct(ExternalFileEditor $editor, FileFormat $fileFormat, $timeout = 0)
    {
        $this->editor = $editor;
        $this->fileFormat = $fileFormat;
        $this->timeout = $timeout;
    }

    /**
     * Bring i/o into scope
     *
     * {@inheritDoc}
     */
    function init(InputInterface $input, OutputInterface $output, Application $application)
    {
        $this->input = $input;
        $this->output = $output;
    }

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
     * Upon error, users will be placed back into the editor, seeded with the
     * previous content.
     *
     * Empty file aborts
     *
     * @param IssueTracker $tracker
     * @param Issue|null $existing
     * @return NewIssue|null
     */
    protected function getNewIssue(IssueTracker $tracker, Issue $existing = null)
    {
        $mapping = $tracker->getMapping();
        $expectations = $mapping->getExpectedDetails($existing);
        $template = $this->fileFormat->seed($expectations);

        if ($this->input->getOption('template')) {
            $this->output->writeln($template);
            exit;
        }

        do {
            if (!empty($e)) {
                $this->outputViolations(array($e->getMessage()));
            }
            if (!empty($details)) {
                $this->outputViolations($details->getViolations());
            }

            if (!$content = trim($this->editor->getEdited($template))) {
                return;
            }

            try {
                $e = null;
                $details = $this->fileFormat->parse($content);
            } catch (\InvalidArgumentException $e) { }

            $template = $content;

        } while ($e or !$details->satisfy($expectations));

        return $mapping->toNewIssue($details->getDetails());
    }

    /**
     * Prints the violations to the user
     *
     * @param array $violations
     */
    protected function outputViolations(array $violations)
    {
        foreach ($violations as $violation) {
            $this->output->writeln("<error>$violation</error>");
        }

        if ($this->timeout) {
            sleep($this->timeout);
        }
    }
}
