<?php

namespace Qissues\Model;

use Qissues\System\DataType\ReadOnlyArrayAccess;

class Issue extends ReadOnlyArrayAccess
{
    private $id;
    private $title;
    private $description;

    public function __construct($id = null, $title, $description)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStatus()
    {
        return new Status('open', 1, 'Open!');
    }

    public function getPriority()
    {
        return new Priority(5, 'busted');
    }

    public function getType()
    {
        return new Type('wat');
    }

    public function getAssignee()
    {
        return new User('adrian', 1, 'Adrian!');
    }

    public function getDateCreated()
    {
        return new \DateTime();
    }

    public function getDateUpdated()
    {
        return new \DateTime();
    }

    public function getCommentCount()
    {
        return 0;
    }
}
