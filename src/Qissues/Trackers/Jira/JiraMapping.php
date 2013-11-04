<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Label;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Querying\SearchCriteria;

class JiraMapping implements FieldMapping
{
    protected $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

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
                'priority' => $issue->getPriority()->getPriority(),
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
            'priority' => '',
            'description' => ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        return new Issue(
            substr($issue['key'], strpos($issue['key'], '-') + 1),
            $issue['fields']['summary'],
            $issue['fields']['description'],
            new Status($issue['fields']['status']['name']),
            new \DateTime($issue['fields']['created']),
            new \DateTime($issue['fields']['updated']),
            !empty($issue['fields']['assignee']) ? new User($issue['fields']['assignee']['name']) : null,
            !empty($issue['fields']['priority']) ? new Priority($issue['fields']['priority']['id'], $issue['fields']['priority']['name']) : null,
            !empty($issue['issuetype']['type']) ? new Type($issue['issuetype']['type']) : null
            // TODO labels?
            // TODO comments?
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewIssue(array $input)
    {
        if (!empty($input['priority'])) {
            if (intval($input['priority'])) {
                $reversePriorities = array_flip($this->priorities);
                $input['priority'] = $reversePriorities[$input['priority']];
            }
        }

        return new NewIssue(
            $input['title'],
            $input['description'],
            !empty($input['assignee']) ? new User($input['assignee']) : null,
            !empty($input['priority']) ? new Priority(null, $input['priority']) : null,
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
            throw new \DomainException('Jira only supports a single label/component.');
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
        if ($priority = $issue->getPriority()) {
            $new['priority'] = $priority->getName();
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
            new User($comment['author']['name']),
            new \DateTime($comment['created'])
        );
    }

    public function buildSearchQuery(SearchCriteria $criteria)
    {
        $query = array();
        $query['project'] = $this->project;

        if ($assignees = $criteria->getAssignees()) {
            $query['assignee'] = array_map('strval', $assignees);
        }

        if ($statuses = $criteria->getStatuses()) {
            $query['status'] = array_map('strval', $statuses);
        }

        if ($types = $criteria->getTypes()) {
            $query['issuetype'] = array_map('strval', $types);
        }

        return $query;

        /*

        if (!empty($options['status'])) {
            if (in_array('open', $options['status'])) {
                $where[] = 'resolution = Unresolved';
                $options['status'] = array_diff($options['status'], array('open'));
            }
            if (!empty($options['status'])) {
                $where[] = 'status IN (' . implode(',', array_map($quote, $options['status'])) . ')';
            }
        }

        $sortMapping = array(
            'priority' => 'priority DESC',
            'updated' => 'updatedDate DESC',
            'created' => 'createdDate DESC'
        );
        $sort = array();
        foreach ($options['sort'] as $by) {
            if (isset($sortMapping[$by])) {
                $sort[] = $sortMapping[$by];
            }
        }

        return urlencode(sprintf(
            '%s ORDER BY %s',
            implode(' AND ', $where),
            implode(', ', $sort)
        ));
*/

        if ($types = $criteria->getTypes()) {
            $validTypes = array('bug', 'enhancement', 'proposal', 'task');
            foreach ($types as $type) {
                if (!in_array($type->getName(), $validTypes)) {
                    throw new \DomainException('That is an unknown type to Jira');
                }
                $query['kind'][] = $type->getName();
            }
        }

        if ($statuses = $criteria->getStatuses()) {
            $validStatuses = array('new', 'open', 'resolved', 'on hold', 'invalid', 'duplicate', 'wontfix');
            foreach ($statuses as $status) {
                if (!in_array($status->getStatus(), $validStatuses)) {
                    throw new \DomainException("'$status' is an unknown status to Jira");
                }
                $query['status'][] = $status->getStatus();
            }
        }

        if ($assignees = $criteria->getAssignees()) {
            foreach ($assignees as $assignee) {
                $query['responsible'][] = $assignee->getAccount();
            }
        }

        if ($labels = $criteria->getLabels()) {
            foreach ($labels as $label) {
                $query['component'][] = $label->getName();
            }
        }

        if ($priorities = $criteria->getPriorities()) {
            foreach ($priorities as $priority) {
                if (!in_array($name = $priority->getName(), array('trivial', 'minor', 'major', 'critical', 'blocker'))) {
                    throw new \DomainException("'$name' is an unsupported priority for Jira");
                }
                $query['priority'][] = $priority->getName();
            }
        }

        if ($keywords = $criteria->getKeywords()) {
            $query['search'] = $keywords;
        }

        if ($criteria->getNumbers()) {
            throw new \DomainException('Jira does not support querying by multiple numbers');
        }

        list($page, $limit) = $criteria->getPaging();
        $query['limit'] = $limit;
        $query['offset'] = ($page - 1) * $limit;

        return $query;
    }
}

