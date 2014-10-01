<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\RichIssue;
use Qissues\Domain\Model\IssueContent;
use Qissues\Domain\Model\IssueMetadata;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Milestone;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
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
    public function getExpectedDetails(Issue $issue = null)
    {
        if ($issue) {
            return new ExpectedDetails(array(
                new ExpectedDetail('title', true, $issue->getTitle()),
                new ExpectedDetail('description', false),
                new ExpectedDetail('type', true, strval($issue->getType()), $this->metadata->getAllowedTypes()),
                new ExpectedDetail('assignee', false, $issue->getAssignee() ? $issue->getAssignee()->getAccount() : ''),
                new ExpectedDetail('priority', false, 3, range(1, 5)),
                new ExpectedDetail('labels', false, $issue->getLabels() ? array_map('strval', $issue->getLabels()) : '', $this->metadata->getAllowedLabels()),
                new ExpectedDetail('milestone', false, $issue->getMetadata()->getMilestone(), $this->metadata->getAllowedSprints()),
            ));
        }

        return new ExpectedDetails(array(
            new ExpectedDetail('title'),
            new ExpectedDetail('description', false),
            new ExpectedDetail('assignee', false),
            new ExpectedDetail('type', true, '', $this->metadata->getAllowedTypes()),
            new ExpectedDetail('priority', false, 3, range(1, 5)),
            new ExpectedDetail('labels', false, '', $this->metadata->getAllowedLabels()),
            new ExpectedDetail('milestone', false, '', $this->metadata->getAllowedSprints())
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        return new RichIssue(
            new Number(substr($issue['key'], strpos($issue['key'], '-') + 1)),
            new IssueContent(
                $issue['fields']['summary'],
                $issue['fields']['description']
            ),
            $this->toIssueMetadata($issue)
        );
    }

    /**
     * Creates the metadata for the issue from jira
     *
     * @param array $issue
     * @return IssueMetadata
     */
    protected function toIssueMetadata(array $issue)
    {
        $metadata = new IssueMetadata(
            new Status($issue['fields']['status']['name']),
            new \DateTime($issue['fields']['created']),
            new \DateTime($issue['fields']['updated'])
        );

        if (!empty($issue['fields']['issuetype'])) {
            $metadata->setType(new Type($issue['fields']['issuetype']['name']));
        }
        if (!empty($issue['fields']['components'])) {
            $metadata->setLabels(array_map(
                function($component) { return new Label($component['name'], $component['id']); },
                $issue['fields']['components']
            ));
        }
        if (!empty($issue['fields']['assignee'])) {
            $metadata->setAssignee(new User($issue['fields']['assignee']['name']));
        }
        if (!empty($issue['fields']['priority'])) {
            $metadata->setPriority(new Priority($issue['fields']['priority']['id'], $issue['fields']['priority']['name']));
        }

        return $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function toNewIssue(array $input)
    {
        $metadata = new IssueMetadata(new Status('new'), new \DateTime, new \DateTime);

        if (!empty($input['assignee'])) {
            $metadata->setAssignee(new User($input['assignee']));
        }
        if (!empty($input['priority'])) {
            $metadata->setPriority(new Priority(null, $input['priority']));
        }
        if (!empty($input['type'])) {
            $metadata->setType(new Type($input['type']));
        }
        if (!empty($input['labels'])) {
            $metadata->setLabels($this->prepareLabels($input['labels']));
        }
        if(!empty($input['milestone'])) {
            $metadata->setMilestone(new Milestone($input['milestone']));
        }

        return new NewIssue(
            new IssueContent($input['title'], $input['description']),
            $metadata
        );
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
                'summary' => $issue->getContent()->getTitle(),
                'description'  => $issue->getContent()->getDescription(),
                'issuetype' => array('id' => $this->metadata->getTypeIdByName($issue->getMetadata()->getType()->getName()))
            )
        );

        if ($assignee = $issue->getMetadata()->getAssignee()) {
            $new['fields']['assignee'] = array(
                'name' => $assignee->getAccount()
            );
        }
        if ($labels = $issue->getMetadata()->getLabels()) {
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

    public function milestoneToSprint(Milestone $milestone)
    {
        return $this->metadata->getMatchingMilestone($milestone->getName());
    }
}
