<?php

namespace Qissues\Trackers\Trello;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Model\NewIssue;
use Qissues\Domain\Model\NewComment;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Trackers\Shared\FieldMapping;
use Qissues\Trackers\Shared\Metadata\Metadata;
use Qissues\Domain\Model\SearchCriteria;
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
            $description = $issue->getDescription();
            if (false !== $pos = strpos($description, "\n\nChecklists:\n\n")) {
                $description = trim(substr($description, 0, $pos));
            }

            return array(
                'title' => $issue->getTitle(),
                'description' => $description,
                'status' => $issue->getStatus()->getStatus(),
                'assignee' => $issue->getAssignee() ? $issue->getAssignee()->getAccount() : null,
                'labels' => $issue->getLabels()
                    ? implode(', ', array_map('strval', $issue->getLabels()))
                    : ''
            );
        }

        return array(
            'title' => '',
            'status' => $this->metadata->getFirstListName(),
            'labels' => '',
            'assignee' => '',
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
            $issue['desc'] .= "\n\nChecklists:";
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

        $user = null;
        if (!empty($input['assignee'])) {
            $id = $this->metadata->getMemberIdByName($input['assignee']);
            $name = $this->metadata->getMemberNameById($id);
            $user = new User($name, $id);
        }

        return new NewIssue(
            $input['title'],
            $input['description'],
            $user,
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

        if ($user = $issue->getAssignee()) {
            $new['idMembers'] = $user->getId();
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
            $query['params']['card_list'] = true;
            $query['params']['idBoards'] = $this->metadata->getBoardId();
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

            if ($assignees = $criteria->getAssignees()) {
                if (!$issue->getAssignee()) {
                    continue;
                }
                foreach ($assignees as $assignee) {
                    if (stripos($issue->getAssignee()->getAccount(), $assignee->getAccount()) === false and
                        stripos($issue->getAssignee()->getName(), $assignee->getAccount()) === false) {
                        continue;
                    }
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
