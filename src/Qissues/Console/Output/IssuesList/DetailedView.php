<?php

namespace Qissues\Console\Output\IssuesList;

use Qissues\Model\Tracker\Support\Feature;
use Qissues\Model\Tracker\Support\FeatureSet;
use Qissues\Model\Tracker\Support\SupportLevel;
use Qissues\Console\Output\TableRenderer;

class DetailedView
{
    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    public function render(array $issues, FeatureSet $features, $width, $height)
    {
        $renderIssues = array();
        foreach ($issues as $issue) {
            $title = $issue->getTitle();
            $row = array(
                '#'            => $issue->getId(),
                'Title'        => strlen($title) > $width * 0.4
                    ? (substr($title, 0, $width * 0.4) . '...')
                    : $title,
                'Status'       => $issue['status'],
                'Type'         => $issue['types'],
                'Priority'     => $issue['priority'],
                'Assignee'     => $issue['assignee'],
                'Date Created' => $issue->getDateCreated()->format('Y-m-d g:ia'),
                'Date updated' => $issue->getDateUpdated()->format('Y-m-d g:ia'),
                'Comments'     => $issue->getCommentCount()
            );

            if (!$features->doesSupport('types')) {
                unset($row['Type']);
            } elseif ($row['Type'] and $features->supports('types', 'multiple')) {
                $row['Type'] = implode(', ', array_map('strval', $row['Type']));
            } else {
                $row['Type'] = '';
            }

            if (!$features->doesSupport('priorities')) {
                unset($row['Priority']);
            }

            $renderIssues[] = $row;
        }

        return $this->tableRenderer->render($renderIssues, $width);
    }
}
