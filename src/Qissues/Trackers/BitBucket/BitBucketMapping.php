<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Label;
use Qissues\Model\Tracker\FieldMapping;

class BitBucketMapping implements FieldMapping
{
    /**
     * {@inheritDoc}
     */
    public function getEditFields(Issue $issue = null)
    {
        if ($issue) {
            return array(
                'title' => $issue->getTitle(),
                'assignee' => $issue->getAssignee() ? $issue->getAssignee()->getAccount() : '',
                'description' => $issue->getDescription(),
                'type' => $issue->getType() ? strval($issue->getType()) : '',
                'label' => $issue->getLabels()
                    ? implode(', ', array_map('strval', $issue->getLabels()))
                    : ''
            );
        }

        return array(
            'title' => '',
            'assignee' => 'me',
            'type' => '',
            'label' => '',
            'description' => ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        return new Issue(
            $issue['local_id'],
            $issue['title'],
            $issue['content'],
            new Status($issue['status']),
            new \DateTime($issue['utc_created_on']),
            new \DateTime($issue['utc_last_updated']),
            !empty($issue['responsible']) ? new User($issue['responsible']['username'], null, $issue['responsible']['display_name']) : null,
            null,
            !empty($issue['metadata']['kind']) ? new Type($issue['metadata']['kind']) : null,
            !empty($issue['metadata']['component']) ? new Label($issue['metadata']['component']) : array(),
            !empty($issue['comment_count']) ? intval($issue['comment_count']) : 0
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewIssue(array $input)
    {
        return new NewIssue(
            $input['title'],
            $input['description'],
            !empty($input['assignee']) ? new User($input['assignee']) : null,
            null,
            !empty($input['type']) ? new Type($input['type']) : null,
            !empty($input['label']) ? array($this->prepareLabel($input['label'])) : null
        );
    }

    protected function prepareLabel($label)
    {
        $label = array_map(
            function($l) { return new Label($l); },
            preg_split('/[\s,]+/', $label, -1, PREG_SPLIT_NO_EMPTY)
        );

        if (count($label) > 1) {
            throw new \DomainException('BitBucket only supports a single label/component.');
        }

        return $label[0];
    }

    /**
     * {@inheritDoc}
     */
    public function issueToArray(NewIssue $issue)
    {
        $new = array(
            'title' => $issue->getTitle(),
            'content'  => $issue->getDescription()
        );

        if ($issue->getAssignee()) {
            $new['responsible'] = $issue->getAssignee()->getAccount();
        }
        if ($labels = $issue->getLabels()) {
            $new['component'] = (string)$labels[0];
        }
        if ($type = $issue->getType()) {
            $new['kind'] = (string)$type;
        }

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function toComment(array $comment)
    {
        return new Comment(
            $comment['content'] ?: '(made some changes)',
            new User(
                $comment['author_info']['username'],
                null,
                $comment['author_info']['display_name']
            ),
            new \DateTime($comment['utc_created_on'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function commentToArray(NewComment $comment)
    {
        return array(
            'body' => $comment->getMessage(),
            'user' => array(
                'login' => $comment->getAuthor()->getAccount(),
                'id' => $comment->getAuthor()->getId()
            ),
            'created_at' => $comment->getDate()
        );
    }
}
