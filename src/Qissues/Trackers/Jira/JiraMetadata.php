<?php

namespace Qissues\Trackers\Jira;

class JiraMetadata
{
    protected $project;

    public function __construct(array $project)
    {
        $this->project = $project;
    }

    public function getId()
    {
        return $this->project['id'];
    }

    public function getKey()
    {
        return $this->project['key'];
    }
}
