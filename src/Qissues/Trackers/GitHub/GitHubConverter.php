<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\NewIssue;
use Qissues\Model\NewComment;
use Qissues\Model\User;
use Qissues\Format\IssueConverter;
use Qissues\Format\CommentConverter;

class GitHubConverter implements IssueConverter, CommentConverter
{
    public function getFields()
    {
        return array(
            'title' => '',
            'assignee' => 'me'
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
            $issue['body']
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
            $input['description']
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

        if (!empty($issue['labels'])) {
            $new['labels'] = $issue['labels'];
        }
        if (!empty($issue['milestone'])) {
            $new['milestone'] = $issue['milestone'];
        }
        if (!empty($issue['assignee'])) {
            $new['assignee'] = $issue['assignee'];
        }

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
