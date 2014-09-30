<?php

namespace Qissues\Domain\Model;

class RichIssue implements Issue
{
    public function __construct(Number $number, IssueContent $content, IssueMetadata $metadata)
    {
        $this->number = $number;
        $this->content = $content;
        $this->metadata = $metadata;
    }

    public function getId()
    {
        return (string)$this->number;
    }

    public function getTitle()
    {
        return $this->content->getTitle();
    }

    public function getDescription()
    {
        return $this->content->getDescription();
    }

    public function getStatus()
    {
        return $this->metadata->getStatus();
    }

    public function getPriority()
    {
        return $this->metadata->getPriority();
    }

    public function getType()
    {
        return $this->metadata->getType();
    }

    public function getAssignee()
    {
        return $this->metadata->getAssignee();
    }

    public function getDateCreated()
    {
        return $this->metadata->getDateCreated();
    }

    public function getDateUpdated()
    {
        return $this->metadata->getDateUpdated();
    }

    public function getCommentCount()
    {
        return $this->metadata->getCommentCount();
    }

    public function getLabels()
    {
        return $this->metadata->getLabels();
    }

    public function getMilestone()
    {
        return $this->metadata->getMilestone();
    }
}
