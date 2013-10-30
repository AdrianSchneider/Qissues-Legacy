<?php

namespace Qissues\Model;

use Qissues\System\DataType\ReadOnlyArrayAccess;

class NewIssue extends ReadOnlyArrayAccess
{
    private $title;
    private $description;

    public function __construct($title, $description)
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
