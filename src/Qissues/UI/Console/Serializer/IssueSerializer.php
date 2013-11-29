<?php

namespace Qissues\Model\Serializer;

use Qissues\Model\Issue;

class IssueSerializer
{
    /**
     * Serializes an Issue into something flat
     * @param Issue @issue
     * @return array serialized representation
     */
    public function serialize(Issue $issue)
    {
        return array(
            'number'      => $issue->getId(),
            'title'       => $issue->getTitle(),
            'description' => $issue->getDescription(),
            'status'      => (string)$issue->getStatus(),
            'type'        => (string)$issue->getType(),
            'labels'      => array_map('strval', $issue->getLabels()),
            'priority'    => strval($issue->getPriority()) ? strval($issue->getPriority()) : 0,
            'assignee'    => (string)$issue->getAssignee(),
            'dateCreated' => $issue->getDateCreated()->format('Y-m-d g:ia'),
            'dateUpdated' => $issue->getDateUpdated()->format('Y-m-d g:ia'),
        );
    }
}
