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

    public function getMatchingStatusName($name)
    {
        $components = array();
        foreach ($this->project['components'] as $component) {
            if (stripos($component['name'], $name) !== false) {
                return $component['name'];
            }

            $components[] = $component['name'];
        }

        throw new \Exception('Label not found; supported labels: ' . implode(', ', $components));
    }

    public function getMatchingMilestone($name)
    {
        $milestones = array();

        foreach ($this->project['sprints'] as $sprint) {
            if (stripos($sprint['name'], $name) !== false) {
                return $sprint['id'];
            }

            $milestones[] = $sprint['name'];
        }

        throw new \Exception('Milestone not found; supported milestones: ' . implode(', ', $milestones));
    }

    public function getAllowedTypes()
    {
        $types = array();
        foreach ($this->project['types'] as $type) {
            $types[] = $type['name'];
        }

        return $types;
    }

    public function getAllowedLabels()
    {
        $labels = array();
        foreach ($this->project['components'] as $component) {
            $labels[] = $component['name'];
        }

        return $labels;
    }

    public function getAllowedSprints()
    {
        $sprints = [];
        foreach ($this->project['sprints'] as $sprint) {
            $sprints[] = $sprint['name'];
        }
        return $sprints;
    }

    public function getAllowedStatuses()
    {
        return $this->project['statuses'];
    }
}
