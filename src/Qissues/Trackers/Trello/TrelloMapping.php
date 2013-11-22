<?php

namespace Qissues\Trackers\Trello;

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
use Qissues\Model\Tracker\Metadata\Metadata;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\System\Util\LocalTime;

class TrelloMapping implements FieldMapping
{
    protected $metadata;

    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getEditFields(Issue $issue = null)
    {
        if ($issue) {
            return array(
                'title' => $issue->getTitle(),
                'description' => $issue->getDescription(),
                'status' => $issue->getStatus()->getStatus(),
                'labels' => $issue->getLabels()
                    ? implode(', ', array_map('strval', $issue->getLabels()))
                    : ''
            );
        }

        return array(
            'title' => '',
            'status' => $this->metadata->getFirstListName(),
            'labels' => '',
            'description' => '',
            'priority' => 'bottom'
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
            LocalTime::convert(new \DateTime($issue['dateLastActivity'])),
            LocalTime::convert(new \DateTime($issue['dateLastActivity'])),
            !empty($issue['members']) ? new User($issue['members'][0]['username'], $issue['members'][0]['id'], $issue['members'][0]['fullName']) : null,
            null,
            null,
            !empty($issue['labels']) ? array_map(function($label) {
                return new Label($label['name'], $label['color']);
            }, $issue['labels']) : array(),
            !empty($issue['actions']) ? count($issue['actions']) : null
        );
    }

    /**
     * {@inheritDoc}
     */
    public function toNewIssue(array $input)
    {
        if (!empty($input['priority'])) {
            if (!in_array($input['priority'], array('top', 'bottom'))) {
                throw new \DomainException('Trello supports top and bottom priorities');
            }
        }

        return new NewIssue(
            $input['title'],
            $input['description'],
            $assignee = null,
            !empty($input['priority']) ? new Priority($input['priority'] == 'top' ? 5 : 1, $input['priority']) : null,
            $type = null,
            !empty($input['labels']) ? $this->prepareLabels($input['labels']) : null,
            new Status($input['status'], $this->metadata->getListIdByName($input['status']))
        );
    }

    protected function prepareLabels($labels)
    {
        $metadata = $this->metadata;
        return array_map(
            function($label) use ($metadata) { 
                $id = $metadata->getLabelIdByName($label);
                $name = $metadata->getLabelNameById($id);
                return new Label($name, $id);
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
            'name' => $issue->getTitle(),
            'desc'  => $issue->getDescription(),
            'idList' => $issue->getStatus()->getId(),
            'due' => null
        );

        if ($issue->getPriority()) {
            $new['pos'] = $issue->getPriority()->getName();
        }
        if ($labels = $issue->getLabels()) {
            $new['labels'] = implode(',', array_map(function($label) {
                return $label->getId();
            }, $labels));
        }

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
            LocalTime::convert(new \DateTime($comment['date']))
        );
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

        $query['params']['actions'] = 'commentCard';
        $query['params']['actions_entities'] = true;
        $query['params']['action_fields'] = 'id';
        $query['params']['members'] = true;

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
            $matchesStatus = false;

            if ($statuses = $criteria->getStatuses()) {
                foreach ($criteria->getStatuses() as $status) {
                    if (stripos($issue->getStatus()->getStatus(), $status->getStatus()) !== false) {
                        $matchesStatus = true;
                        break;
                    }
                }
                if (!$matchesStatus) {
                    continue;
                }
            }

            $matchesLabel = false;
            if ($labels = $criteria->getLabels()) {
                foreach ($criteria->getLabels() as $label) {
                    foreach ($issue->getLabels() as $issueLabel) {
                        if (stripos($issueLabel->getName(), $label->getName()) !== false) {
                            $matchesLabel = true;
                            break 2;
                        }
                    }
                }
                if (!$matchesLabel) {
                    continue;
                }
            }

            $out[] = $issue;
        }

        if ($sortFields = $criteria->getSortFields()) {
            if (count($sortFields) > 1) {
                throw new \DomainException('Cannot multisort on Trello');
            }

            $sortField = $sortFields[0];
            $validSortFields = array('priority', 'updated', 'created');

            if (!in_array($sortField, $validSortFields)) {
                throw new \DomainException("Sorting by $sortField is unsupported on Trello");
            }

            $numericSort = function($a, $b) { if ($a == $b) { return 0; } return $a > $b ? -1 : 1; };

            if ($sortField == 'created') {
                usort($out, function($a, $b) use ($numericSort) {
                    return $numericSort($a->getId(), $b->getId());
                });
            }
            if ($sortField == 'updated') {
                usort($out, function($a, $b) use ($numericSort) {
                    return $numericSort($a->getDateUpdated(), $b->getDateUpdated());
                });
            }
        }

        return $out;
    }
}
