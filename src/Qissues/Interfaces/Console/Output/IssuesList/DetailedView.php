<?php

namespace Qissues\Interfaces\Console\Output\IssuesList;

use Qissues\Application\Tracker\Support\Feature;
use Qissues\Application\Tracker\Support\FeatureSet;
use Qissues\Application\Tracker\Support\SupportLevel;
use Qissues\Interfaces\Console\Output\TableRenderer;

class DetailedView
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
            $title = $issue->getTitle();
            $row = array(
                '#'            => $issue->getId(),
                'Title'        => strlen($title) > $width * 0.4
                    ? (substr($title, 0, $width * 0.4) . '...')
                    : $title,
                'Status'       => $issue['status'],
                'Type'         => $issue['type'],
                'Labels'       => $issue['labels'],
                'Priority'     => $issue['priority'],
                'Assignee'     => $issue['assignee'],
                'Date Created' => $issue->getDateCreated()->format('Y-m-d g:ia'),
                'Date updated' => $issue->getDateUpdated()->format('Y-m-d g:ia'),
                'Comments'     => $issue->getCommentCount()
            );

            if (!$features->doesSupport('labels')) {
                unset($row['Label']);
            } elseif ($row['Labels'] and $features->supports('labels', 'multiple')) {
                $row['Labels'] = implode(', ', array_map('strval', $row['Labels']));
            } else {
                $row['Labels'] = '';
            }

            if (!$features->doesSupport('types')) {
                unset($row['Type']);
            }
            if (!$features->doesSupport('priorities')) {
                unset($row['Priority']);
            }

            $renderIssues[] = $row;
        }

        return $this->tableRenderer->render($renderIssues, $width);
    }
}
