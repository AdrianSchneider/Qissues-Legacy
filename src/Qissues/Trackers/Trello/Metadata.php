<?php

namespace Qissues\Trackers\Trello;

class Metadata
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

        throw new \LogicException('Could not find trello status; update metadata');
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
}
