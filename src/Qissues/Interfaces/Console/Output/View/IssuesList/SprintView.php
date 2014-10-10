<?php

namespace Qissues\Interfaces\Console\Output\View\IssuesList;

use Qissues\Domain\Model\Issue;
use Qissues\Application\Tracker\Metadata\Metadata;
use Qissues\Domain\Model\Response\Issues;
use Qissues\Application\Tracker\Support\Feature;
use Qissues\Application\Tracker\Support\FeatureSet;
use Qissues\Application\Tracker\Support\SupportLevel;
use Qissues\Interfaces\Console\Output\Renderer\TableRenderer;

class SprintView
{
    protected $tableRenderer;
    protected $metadata;

    public function __construct(TableRenderer $tableRenderer, Metadata $metadata)
    {
        $this->tableRenderer = $tableRenderer;
        $this->metadata = $metadata;
    }

    public function render(Issues $issues, FeatureSet $features, $width, $height)
    {
        return $this->tableRenderer->render(
            $this->groupIssuesByStatus($issues, $width),
            $width
        );
    }

    protected function groupIssuesByStatus(Issues $issues, $width)
    {
        $out = [];
        foreach ($issues as $issue) {
            $row = [];
            foreach ($this->metadata->getAllowedStatuses() as $id => $status) {
                $row[$status] = '';
            }

            $row[(string)$issue->getStatus()] = $issue->getId();
            $out[] = $row;
        }

        return $out;
    }

    protected function prepare(Issue $issue, $width, $features)
    {
        $title = $issue->getTitle();
        $row = array(
            '#'            => $issue->getId(),
            'Title'        => strlen($title) > $width * 0.4
                ? (substr($title, 0, $width * 0.4) . '...')
                : $title,
            'Status'       => $issue->getStatus(),
            'Type'         => $issue->getType(),
            'Labels'       => $issue->getLabels(),
            'Priority'     => $issue->getPriority(),
            'Assignee'     => $issue->getAssignee(),
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

        return $row;
    }
}