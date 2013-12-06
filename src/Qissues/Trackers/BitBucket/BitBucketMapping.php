<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Application\Tracker\FieldMapping;
use Qissues\Domain\Model\SearchCriteria;

class BitBucketMapping implements FieldMapping
{
    protected $statuses = array(
        'new',
        'open',
        'resolved',
        'on hold',
        'invalid',
        'duplicate',
        'wontfix'
    );

    protected $types = array(
        'bug',
        'enhancement',
        'proposal',
        'task'
    );

    protected $priorities = array(
        'trivial'  => 1,
        'minor'    => 2,
        'major'    => 3,
        'critical' => 4,
        'blocker'  => 5
    );

    protected $priorityMap;

    public function __construct()
    {
        $this->priorityMap = array_flip($this->priorities);
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
                new ExpectedDetail('type', false, $issue->getType() ? strval($issue->getType()) : ''),
                new ExpectedDetail('label', false, $issue->getLabels() ? implode(', ', array_map('strval', $issue->getLabels())) : ''),
                new ExpectedDetail('priority', false, $issue->getPriority()->getPriority())
            ));
        }

        return new ExpectedDetails(array(
            new ExpectedDetail('title'),
            new ExpectedDetail('description', false),
            new ExpectedDetail('assignee', false),
            new ExpectedDetail('type', false, '', $this->types),
            new ExpectedDetail('label', false),
            new ExpectedDetail('priority', false, 'major', array_keys($this->priorities))
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        return new Issue(
            $issue['local_id'],
            $issue['title'],
            $issue['content'],
            new Status($issue['status']),
            new \DateTime($issue['utc_created_on']),
            new \DateTime($issue['utc_last_updated']),
            !empty($issue['responsible']) ? new User($issue['responsible']['username'], null, $issue['responsible']['display_name']) : null,
            !empty($issue['priority']) ? new Priority($this->priorities[$issue['priority']], $issue['priority']) : null,
            !empty($issue['metadata']['kind']) ? new Type($issue['metadata']['kind']) : null,
            !empty($issue['metadata']['component']) ? array(new Label($issue['metadata']['component'])) : array(),
            !empty($issue['comment_count']) ? intval($issue['comment_count']) : 0
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
            throw new \DomainException('BitBucket only supports a single label/component.');
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
            $comment['content'] ?: '(made some changes)',
            new User(
                $comment['author_info']['username'],
                null,
                $comment['author_info']['display_name']
            ),
            new \DateTime($comment['utc_created_on'])
        );
    }

    public function buildSearchQuery(SearchCriteria $criteria)
    {
        $query = array();

        if ($types = $criteria->getTypes()) {
            foreach ($types as $type) {
                if (!in_array($type->getName(), $this->types)) {
                    throw new \DomainException('That is an unknown type to BitBucket');
                }
                $query['kind'][] = $type->getName();
            }
        }

        if ($statuses = $criteria->getStatuses()) {
            foreach ($statuses as $status) {
                if (!in_array($status->getStatus(), $this->statuses)) {
                    throw new \DomainException("'$status' is an unknown status to BitBucket");
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
                if (isset($this->priorityMap[$priority->getPriority()])) {
                    $query['priority'][] = $this->priorityMap[$priority->getPriority()];
                    continue;
                }
                if (!in_array($name = $priority->getName(), array('trivial', 'minor', 'major', 'critical', 'blocker'))) {
                    throw new \DomainException("'$name' is an unsupported priority for BitBucket");
                }
                $query['priority'][] = $priority->getName();
            }
        }

        if ($keywords = $criteria->getKeywords()) {
            $query['search'] = $keywords;
        }

        if ($criteria->getNumbers()) {
            throw new \DomainException('BitBucket does not support querying by multiple numbers');
        }

        list($page, $limit) = $criteria->getPaging();
        $query['limit'] = $limit;
        $query['offset'] = ($page - 1) * $limit;

        return $query;
    }

    /**
     * Get the matching status
     *
     * @param Status $findStatus
     * @return Status
     * @throws DomainException when not found
     */
    public function getStatusMatching(Status $findStatus)
    {
        foreach ($this->statuses as $status) {
            if (strpos($status, $findStatus->getStatus()) !== false) {
                return new Status($status);
            }
        }

        throw new \DomainException('invalid status; valid statuses: ' . implode(', ', $this->statuses));
    }
}
