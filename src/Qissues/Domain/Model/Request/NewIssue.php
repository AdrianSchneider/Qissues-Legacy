<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\IssueContent;
use Qissues\Domain\Model\IssueMetadata;
    
/**
 * Represents a new issue being created
 */
class NewIssue
{
    public function __construct(IssueContent $content, IssueMetadata $metadata)
    {
        $this->content = $content;
        $this->metadata = $metadata;
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
