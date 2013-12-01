<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Application\Tracker\FieldMapping;
use Qissues\Application\Tracker\Metadata\Metadata;
use Qissues\Domain\Model\SearchCriteria;

class JiraMapping implements FieldMapping
{
    protected $metadata;
    protected $jql;

    /**
     * @param JiraMetadata $metadata
     * @param JqlQueryBuilder $jql
     */
    public function __construct(Metadata $metadata, JqlQueryBuilder $jql)
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
                'labels' => $issue->getLabels()
                    ? implode(', ', array_map('strval', $issue->getLabels()))
                    : ''
            );
        }

        return array(
            'title' => '',
            'assignee' => 'me',
            'type' => '',
            'labels' => '',
            'priority' => '',
            'description' => ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        $assignee = $priority = $type = $labels = null;

        if (!empty($issue['fields']['assignee'])) {
            $assignee = new User($issue['fields']['assignee']['name']);
        }
        if (!empty($issue['fields']['priority'])) {
            $priority = new Priority($issue['fields']['priority']['id'], $issue['fields']['priority']['name']);
        }
        if (!empty($issue['fields']['components'])) {
            $labels = array();
            foreach ($issue['fields']['components'] as $component) {
                $labels[] = new Label($component['name'], $component['id']);
            }
        }
        if (!empty($issue['fields']['issuetype'])) {
            $type = new Type($issue['fields']['issuetype']['name']);
        }

        return new Issue(
            substr($issue['key'], strpos($issue['key'], '-') + 1),
            $issue['fields']['summary'],
            $issue['fields']['description'],
            new Status($issue['fields']['status']['name']),
            new \DateTime($issue['fields']['created']),
            new \DateTime($issue['fields']['updated']),
            $assignee,
            $priority,
            $type,
            $labels,
            intval($issue['fields']['comment']['total'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewIssue(array $input)
    {
        $assignee = $priority = $type = $labels = null;

        if (!empty($input['assignee'])) {
            $assignee = new User($input['assignee']);
        }
        if (!empty($input['priority'])) {
            $priority = new Priority(null, $input['priority']);
        }
        if (!empty($input['type'])) {
            $type = new Type($input['type']);
        }
        if (!empty($input['labels'])) {
            $labels = $this->prepareLabels($input['labels']);
        }

        return new NewIssue( $input['title'], $input['description'], $assignee, $priority, $type, $labels);
    }

    protected function prepareLabels($labels)
    {
        $metadata = $this->metadata;
        return array_map(
            function($l) use ($metadata) { 
                return new Label($metadata->getMatchingStatusName($l));
            },
            preg_split('/[\s,]+/', $labels, -1, PREG_SPLIT_NO_EMPTY)
        );
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

        if ($assignee = $issue->getAssignee()) {
            $new['fields']['assignee'] = array(
                'name' => $assignee->getAccount()
            );
        }
        if ($labels = $issue->getLabels()) {
            $new['fields']['components'] = array_map(
                function($label) {
                    return array('name' => $label->getName());
                },
                $labels
            );
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

    /**
     * {@inheritDoc}
     */
    public function buildSearchQuery(SearchCriteria $criteria)
    {
        list($page, $perPage) = $criteria->getPaging();

        return array(
            'fields' => '*all',
            'jql' => $this->jql->build($criteria),
            'startAt' => ($page - 1) * $perPage,
            'maxResults' => $perPage,
        );
    }
}
