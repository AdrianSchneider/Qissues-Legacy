<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Label;
use Qissues\Model\Tracker\FieldMapping;

class GitHubMapping implements FieldMapping
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
                'labels' => $issue->getLabels()
                    ? implode(', ', array_map('strval', $issue->getLabels()))
                    : ''
            );
        }

        return array(
            'title' => '',
            'assignee' => 'me',
            'labels' => '',
            'milestone' => '',
            'description' => ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        return new Issue(
            $issue['number'],
            $issue['title'],
            $issue['body'],
            new Status($issue['state']),
            new \DateTime($issue['created_at']),
            new \DateTime($issue['updated_at']),
            $issue['assignee'] ? new User($issue['assignee']['login']) : null,
            null,
            null,
            $issue['labels'] ? array_map(function($label) {
                return new Label($label['name']);
            }, $issue['labels']) : array()
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
            null,
            !empty($input['labels']) ? $this->prepareLabels($input['labels']) : null
        );
    }

    protected function prepareLabels($labels)
    {
        return array_map(
            function($label) { return new Label($label); },
            preg_split('/[\s,]+/', $labels, -1, PREG_SPLIT_NO_EMPTY)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function issueToArray(NewIssue $issue)
    {
        $new = array(
            'title' => $issue->getTitle(),
            'body'  => $issue->getDescription()
        );

        if ($issue->getAssignee()) {
            $new['assignee'] = $issue->getAssignee()->getAccount();
        }
        if ($labels = $issue->getLabels()) {
            $new['labels'] = array_map('strval', $labels);
        }

        /*
        if (!empty($issue['labels'])) {
            $new['labels'] = $issue['labels'];
        }
        if (!empty($issue['milestone'])) {
            $new['milestone'] = $issue['milestone'];
        }
        if (!empty($issue['assignee'])) {
            $new['assignee'] = $issue['assignee'];
        }
         */

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function toComment(array $comment)
    {
        return new Comment(
            $comment['body'],
            new User(
                $comment['user']['login'],
                $comment['user']['id']
            ),
            new \DateTime($comment['created_at'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewComment(array $comment)
    {
        return new NewComment();
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
