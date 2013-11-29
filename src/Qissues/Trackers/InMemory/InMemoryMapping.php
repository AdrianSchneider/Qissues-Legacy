<?php

namespace Qissues\Trackers\InMemory;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Model\NewIssue;
use Qissues\Domain\Model\NewComment;
use Qissues\Domain\Tracker\FieldMapping;
use Qissues\Domain\Model\SearchCriteria;

class InMemoryMapping implements FieldMapping
{
    /**
     * {@inheritDoc}
     */
    public function getEditFields(Issue $issue = null)
    {
        throw new \DomainException('For testing only');
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        if (empty($issue['status'])) {
            $issue['status'] = 'new';
        }

        return new Issue(
            $issue['number'],
            $issue['title'],
            $issue['description'],
            new Status($issue['status']),
            new \DateTime($issue['created']),
            new \DateTime($issue['updated']),
            !empty($issue['assignee']) ? new User($issue['assignee']) : null,
            null,
            null,
            !empty($issue['labels']) ? array_map(function($label) {
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
            'description'  => $issue->getDescription()
        );

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
            new \DateTime($comment['created'])
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

            $query['status'] = $statuses[0]->getStatus();
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
