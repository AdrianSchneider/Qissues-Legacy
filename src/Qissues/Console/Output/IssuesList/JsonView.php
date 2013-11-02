<?php

namespace Qissues\Console\Output\IssuesList;

use Qissues\Model\Issue;
use Qissues\Model\Tracker\Support\FeatureSet;

class JsonView
{
    public function render(array $issues, FeatureSet $features, $width, $height)
    {
        return json_encode(array_map(array($this, 'toJson'), $issues));
    }

    protected function toJson(Issue $issue)
    {
        return array(
            'number'      => $issue->getId(),
            'title'       => $issue->getTitle(),
            'description' => $issue->getDescription(),
            'status'      => (string)$issue->getStatus(),
            'type'        => (string)$issue->getType(),
            'labels'      => array_map('strval', $issue->getLabels()),
            'priority'    => (string)$issue->getPriority(),
            'assignee'    => (string)$issue->getAssignee(),
            'dateCreated' => $issue->getDateCreated()->format('Y-m-d g:ia'),
            'dateUpdated' => $issue->getDateUpdated()->format('Y-m-d g:ia')
        );
    }
}
