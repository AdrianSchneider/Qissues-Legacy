<?php

namespace Qissues\Interfaces\Console\Output\IssuesList;

use Qissues\Interfaces\Console\Output\TableRenderer;
use Qissues\Domain\Tracker\Support\FeatureSet;

class BasicView
{
    protected $tableRenderer;

    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    public function render(array $issues, FeatureSet $features, $width, $height)
    {
        $renderIssues = array();
        foreach ($issues as $issue) {
            $renderIssues[] = array(
                'Id'           => $issue['id'],
                'Title'        => strlen($issue['title']) > $width * 0.4
                    ? (substr($issue['title'], 0, $width * 0.4) . '...')
                    : $issue['title'],
                'Status'       => $issue['status'],
                'Date updated' => $issue['dateUpdated']->format('Y-m-d g:ia')
            );
        }

        return $this->tableRenderer->render($renderIssues, $width);
    }
}
