<?php

namespace Qissues\Trackers\Trello;

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

class TrelloMapping implements FieldMapping
{
    protected $metadata;

    public function __construct(TrelloMetadataBuilder $builder)
    {
        $this->metadata = $builder->build();
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
                'labels' => $issue->getLabels()
                    ? implode(', ', array_map('strval', $issue->getLabels()))
                    : ''
            );
        }

        return array(
            'title' => '',
            'assignee' => 'me',
            'labels' => '',
            'checklists' => '',
            'description' => ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toIssue(array $issue)
    {
        $status = new Status($this->metadata->getListNameById($issue['idList']));

        if (!empty($issue['checklists'])) {
            foreach ($issue['checklists'] as $checklist) {
                $issue['desc'] .= "\n\n$checklist[name]";
                foreach ($checklist['checkItems'] as $item) {
                    $prefix = $item['state'] == 'complete' ? '[x]' : '[ ]';
                    $issue['desc'] .= "\n    $prefix $item[name]";
                }
            }
        }

        return new Issue(
            $issue['idShort'],
            $issue['name'],
            trim($issue['desc']),
            $status,
            new \DateTime($issue['dateLastActivity']),
            new \DateTime($issue['dateLastActivity']),
            null,#$issue['assignee'] ? new User($issue['assignee']['login']) : null,
            null,
            null,
            !empty($issue['labels']) ? array_map(function($label) {
                return new Label($label['name']);
            }, $issue['labels']) : array()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewIssue(array $input)
    {
        throw new \Exception('wip');
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
            $comment['data']['text'],
            new User(
                $comment['memberCreator']['username'],
                $comment['memberCreator']['fullName']
            ),
            new \DateTime($comment['date'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewComment(array $comment)
    {
        throw new \Exception('wip');
    }

    /**
     * {@inheritDoc}
     */
    public function buildSearchQuery(SearchCriteria $criteria)
    {
        if ($criteria->getPriorities()) {
            throw new \DomainException('Trello cannot search by priority');
        }

        $query = array('params' => array());

        if ($keywords = $criteria->getKeywords()) {
            $query['params']['query'] = $keywords;
            $query['endpoint'] = "/search";

        } elseif (count($statuses = $criteria->getStatuses()) == 1) {
            $query['endpoint'] = sprintf("/lists/%s/cards", $this->metadata->getListIdByName($statuses[0]->getStatus()));
        } else {
            $query['endpoint'] = sprintf("/boards/%s/cards", $this->metadata->getBoardId());
        }

        // TODO sorting

        list($offset, $limit) = $criteria->getPaging();
        list($query['params']['page'], $query['params']['per_page']) = $criteria->getPaging();

        return $query;
    }

    /**
     * Post-query filtering based on search criteria
     *
     * @param Issue[] $issues
     * @param SearchCriteria $criteria
     * @return Issue[] filtered
     */
    public function filterIssues(array $issues, SearchCriteria $criteria)
    {
        $out = array();
        foreach ($issues as $issue) {
            if ($criteria->getStatuses() and !array_intersect(array($issue->getStatus()), $criteria->getStatuses())) {
                continue;
            }
            if ($criteria->getLabels() and !array_intersect($issue->getLabels(), $criteria->getLabels())) {
                continue;
            }

            $out[] = $issue;
        }

        return $out;
    }
}
