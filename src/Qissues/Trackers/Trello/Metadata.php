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
            if ($list['name'] == $name) {
                return $list['id'];
            }
        }

        throw new \LogicException('Could not find trello status; update metadata');
    }

    public function getFirstListName()
    {
        return $this->board['lists'][0]['name'];
    }
}
