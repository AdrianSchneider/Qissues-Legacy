<?php

namespace Qissues\Domain\Model;

class RichIssue
{
    public function __construct(Number $number, IssueContent $content, IssueMetadata $metadata)
    {
        $this->number = $number;
        $this->content = $content;
        $this->metadata = $metadata;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
}
