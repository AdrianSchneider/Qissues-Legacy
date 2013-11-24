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
    protected $metadata;
    protected $jql;

    /**
     * @param JiraMetadata $metadata
     * @param JqlQueryBuilder $jql
     */
    public function __construct(JiraMetadata $metadata, JqlQueryBuilder $jql)
    {
        $this->metadata = $metadata;
        $this->jql = $jql;
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
            'fields' => array(
                'project' => array('id' => $this->metadata->getId()),
                'summary' => $issue->getTitle(),
                'description'  => $issue->getDescription(),
                'issuetype' => array('id' => $this->metadata->getTypeIdByName($issue->getType()->getName()))
            )
        );

        /*
        if ($issue->getAssignee()) {
            $new['responsible'] = $issue->getAssignee()->getAccount();
        }
        if ($labels = $issue->getLabels()) {
            $new['component'] = (string)$labels[0];
        }
        if ($priority = $issue->getPriority()) {
            $new['priority'] = $priority->getName();
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
            new User($comment['author']['name']),
            new \DateTime($comment['created'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function buildSearchQuery(SearchCriteria $criteria)
    {
        list($page, $perPage) = $criteria->getPaging();

        return array(
            'jql' => $this->jql->build($criteria),
            'startAt' => ($page - 1) * $perPage,
            'maxResults' => $perPage,

        );
    }
}
