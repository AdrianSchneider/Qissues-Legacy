<?php

namespace Qissues\Console\Output\Issue;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\Tracker\Support\FeatureSet;

class JsonView
{
    public function render(Issue $issue, $width, $height, array $comments)
    {
        return json_encode($this->issueToJson($issue, $comments));
    }

    protected function issueToJson(Issue $issue, array $comments)
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
            'dateUpdated' => $issue->getDateUpdated()->format('Y-m-d g:ia'),
            'comments'    => array_map(array($this, 'commentToJson'), $comments)
        );
    }

    protected function commentToJson(Comment $comment)
    {
        return array(
            'message' => $comment->getMessage(),
            'author'  => $comment->getAuthor()->getAccount(),
            'date'    => $comment->getDate()->format('Y-m-d g:ia')
        );
    }
}
