<?php

namespace Qissues\Interfaces\Console\Output\View\IssuesList;

use Qissues\Domain\Model\Response\Issues;
use Qissues\Application\Tracker\Support\Feature;
use Qissues\Application\Tracker\Support\FeatureSet;
use Qissues\Application\Tracker\Support\SupportLevel;
use Qissues\Interfaces\Console\Output\Renderer\TableRenderer;

class DetailedView
{
    protected $tableRenderer;

    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    public function render(Issues $issues, FeatureSet $features, $width, $height)
    {
        return $this->tableRenderer->render(
            $issues->map(function($issue) use ($width, $features) {
                return $this->prepare($issue, $width, $features);
            }),
            $width
        );
    }

    protected function prepare($issue, $width, $features)
    {
        $title = $issue->getContent()->getTitle();
        $row = array(
            '#'            => $issue->getNumber(),
            'Title'        => strlen($title) > $width * 0.4
                ? (substr($title, 0, $width * 0.4) . '...')
                : $title,
            'Status'       => $issue->getMetadata()->getStatus(),
            'Type'         => $issue->getMetadata()->getType(),
            'Labels'       => $issue->getMetadata()->getLabels(),
            'Priority'     => $issue->getMetadata()->getPriority(),
            'Assignee'     => $issue->getMetadata()->getAssignee(),
            'Date Created' => $issue->getMetadata()->getDateCreated()->format('Y-m-d g:ia'),
            'Date updated' => $issue->getMetadata()->getDateUpdated()->format('Y-m-d g:ia'),
            'Comments'     => $issue->getMetadata()->getCommentCount()
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
