<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Application\Tracker\FieldMapping;

class GitHubMapping implements FieldMapping
{
    protected $username;

    public function __construct($username = '')
    {
        $this->username = $username;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpectedDetails(Issue $issue = null)
    {
        if ($issue) {
            return new ExpectedDetails(array(
                new ExpectedDetail('title', true, $issue->getTitle()),
                new ExpectedDetail('description', false, $issue->getDescription()),
                new ExpectedDetail('assignee', false, $issue->getAssignee() ? $issue->getAssignee()->getAccount() : ''),
                new ExpectedDetail('labels', false, $issue->getLabels() ? implode(', ', array_map('strval', $issue->getLabels())) : '')
            ));
        }

        return new ExpectedDetails(array(
            new ExpectedDetail('title'),
            new ExpectedDetail('description', false),
            new ExpectedDetail('assignee', false),
            new ExpectedDetail('labels', false),
            new ExpectedDetail('milestone', false)
        ));
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

        if ($assignees = $criteria->getAssignees()) {
            if (count($assignees) > 1) {
                throw new \DomainException('Github cannot support multiple assignees');
            }
            if ($assignees[0] instanceof CurrentUser) {
                $query['assignee'] = $this->username;
            } else {
                $query['assignee'] = $assignees[0]->getAccount();
            }

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
