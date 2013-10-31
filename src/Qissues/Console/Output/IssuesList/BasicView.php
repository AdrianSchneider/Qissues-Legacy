<?php

namespace Qissues\Console\Output\IssuesList;

use Qissues\Console\Output\TableRenderer;
use Qissues\Model\Tracker\Support\FeatureSet;

class BasicView
{
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
                'Type'         => $issue['type'],
                'P'            => $issue['priority'],
                'Date updated' => $issue['dateUpdated']->format('Y-m-d g:ia')
            );
        }

        return $this->tableRenderer->render($renderIssues, $width);
    }
}