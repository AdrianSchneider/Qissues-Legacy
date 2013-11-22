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
use Qissues\Model\Querying\SearchCriteria;

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
            }, $issue['labels']) : array(),
            !empty($issue['comments']) ? intval($issue['comments']) : 0
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
    public function buildSearchQuery(SearchCriteria $criteria)
    {
        $query = array();

        $sortFields = $criteria->getSortFields() ?: array('updated');
        $validFields = array('created', 'updated', 'comments');

        if (count($sortFields) > 1) {
            throw new \DomainException('GitHub cannot multi-sort');
        }
        if (!in_array($sortFields[0], $validFields)) {
            throw new \DomainException("Sorting by '$sortFields[0]' is unsupported on GitHub");
        }

        $query['sort'] = $sortFields[0];

        if ($statuses = $criteria->getStatuses()) {
            if (count($statuses) > 1) {
                throw new \DomainException('GitHub cannot support multiple statuses');
            }

            $query['state'] = $statuses[0]->getStatus();
        }

        if ($labels = $criteria->getLabels()) {
            $query['labels'] = implode(',', array_map('strval', $labels));
        }

        if ($criteria->getNumbers()) {
            throw new \DomainException('Github cannot search by multiple numbers');
        }
        if ($criteria->getKeywords()) {
            throw new \DomainException('Github cannot search by keywords');
        }
        if ($criteria->getPriorities()) {
            throw new \DomainException('Github cannot search by priority');
        }

        list($offset, $limit) = $criteria->getPaging();
        list($query['page'], $query['per_page']) = $criteria->getPaging();

        return $query;
    }
}
