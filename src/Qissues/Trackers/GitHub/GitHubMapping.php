<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Type;
use Qissues\Model\Tracker\FieldMapping;

class GitHubMapping implements FieldMapping
{
    public function map($dtoField)
    {

    }

    public function reverseMap($issueField)
    {

    }

    public function getEditFields(Issue $issue = null)
    {
        if ($issue) {
            return array(
                'title' => $issue->getTitle(),
                'assignee' => $issue->getAssignee() ? $issue->getAssignee()->getAccount() : '',
                'description' => $issue->getDescription()
            );
        }

        return array(
            'title' => '',
            'assignee' => 'me',
            'types' => '',
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
            $issue['labels'] ? array_map(function($label) {
                return new Type($label['name']);
            }, $issue['labels']) : array()
        );

        array(
            'assignee'      => $issue['assignee'] ? $issue['assignee']['login'] : '',
            'created'       => new \DateTime($issue['created_at']),
            'updated'       => new \DateTime($issue['updated_at']),
            'status'        => $issue['state'],
            'priority'      => 1,
            'priority_text' => 'n/a',
            'type'          => 'TODO',
            'comments'      => $issue['comments']
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
            !empty($input['assignee']) ? new User($input['assignee']) : null
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
