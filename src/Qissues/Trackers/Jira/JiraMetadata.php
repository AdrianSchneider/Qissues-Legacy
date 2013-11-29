<?php

namespace Qissues\Trackers\Jira;

use Qissues\Application\Tracker\Metadata\Metadata;

class JiraMetadata implements Metadata
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

    public function getTypeIdByName($name)
    {
        foreach ($this->project['types'] as $type) {
            if (stripos($type['name'], $name) !== false) {
                return $type['id'];
            }
        }

        throw new \Exception('not found');
    }
}
