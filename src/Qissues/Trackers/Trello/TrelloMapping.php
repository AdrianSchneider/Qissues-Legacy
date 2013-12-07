<?php

namespace Qissues\Trackers\Trello;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Application\Tracker\FieldMapping;
use Qissues\Application\Tracker\Metadata\Metadata;
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
    public function getExpectedDetails(Issue $issue = null)
    {
        if ($issue) {
            $description = $issue->getDescription();
            if (false !== $pos = strpos($description, "\n\nChecklists:\n\n")) {
                $description = trim(substr($description, 0, $pos));
            }

            return new ExpectedDetails(array(
                new ExpectedDetail('title', true, $issue->getTitle()),
                new ExpectedDetail('description', false, $description),
                new ExpectedDetail('status', true, $issue->getStatus()->getStatus()),
                new ExpectedDetail('labels', false, $issue->getLabels() ? implode(', ', array_map('strval', $issue->getLabels())) : ''),
                new ExpectedDetail('assignee', false, $issue->getAssignee() ? $issue->getAssignee()->getAccount : null),
                new ExpectedDetail('priority', false, 'bottom', array('bottom', 'top'))
            ));
        }

        return new ExpectedDetails(array(
            new ExpectedDetail('title'),
            new ExpectedDetail('description', false),
            new ExpectedDetail('status', true, $this->metadata->getFirstListName(), $this->metadata->getAllowedLists()),
            new ExpectedDetail('labels', false),
            new ExpectedDetail('assignee', false),
            new ExpectedDetail('priority', false, 'bottom', array('bottom', 'top'))
        ));
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
}
