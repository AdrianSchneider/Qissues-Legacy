<?php

namespace Qissues\Domain\Model;

class IssueContent
{
    public function __construct($title, $description, $blurb = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->blurb = $blurb;
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
