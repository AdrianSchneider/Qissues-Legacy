<?php

namespace Qissues\Trackers\Trello;

use Qissues\Application\Tracker\Metadata\Metadata;

class TrelloMetadata implements Metadata
{
    protected $board;
    public function __construct(array $board)
    {
        $this->board = $board;
    }

    public function getBoardId()
    {
        return $this->board['id'];
    }

    public function hasList($name)
    {
        foreach ($this->board['lists'] as $list) {
            if ($list['name'] == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a list name by its ID
     *
     * @param string $id
     * @return string list name
     * @throws LogicException when not found
     */
    public function getListNameById($id)
    {
        foreach ($this->board['lists'] as $list) {
            if ($list['id'] == $id) {
                return $list['name'];
            }
        }

        throw new \LogicException('Could not find trello status; update metadata');
    }

    /**
     * Find a list ID by its name
     *
     * @param string $name
     * @return string list id
     * @throws LogicException when not found
     */
    public function getListIdByName($name)
    {
        foreach ($this->board['lists'] as $list) {
            if (stripos($list['name'], $name) !== false) {
                return $list['id'];
            }
        }

        $statuses = array();
        foreach ($this->board['lists'] as $list) {
            $statuses[] = $list['name'];
        }


        throw new \LogicException('Could not find trello status; valid statuses: '  . implode(',', $statuses));
    }

    /**
     * Get the first list's name
     * @return string
     */
    public function getFirstListName()
    {
        return $this->board['lists'][0]['name'];
    }

    public function getLabelNameById($id)
    {
        foreach ($this->board['labels'] as $color => $label) {
            if ($color === $id) {
                return $label;
            }
        }

        throw new \LogicException('Could not find trello label; update metadata');
    }

    public function getLabelIdByName($query)
    {
        foreach ($this->board['labels'] as $color => $name) {
            if (stripos($name, $query) !== false) {
                return $color;
            }
        }

        throw new \LogicException('Could not find trello label; update metadata');
    }

    public function getMemberNameById($id)
    {
        foreach ($this->board['members'] as $member) {
            if ($id == $member['id']) {
                return $member['username'];
            }
        }

        throw new \LogicException('Member not found; try updating metadata');
    }

    public function getMemberIdByName($name)
    {
        $names = array();
        foreach ($this->board['members'] as $member) {
            if (stripos($member['username'], $name) !== false or stripos($member['fullName'], $name) !== false) {
                return $member['id'];
            }
            $names[] = $member['username'];
        }

        $members = implode(', ', $names);
        throw new \LogicException("$name not found; available members: $members");
    }
}
