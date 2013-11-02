<?php

namespace Qissues\Model\Tracker;

use Qissues\Model\Issue;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;

interface FieldMapping
{
    /**
     * Prepare fields for editing
     * @param Issue|null $issue, if existing
     * @return array fields => values
     */
    function getEditFields(Issue $issue = null);

    /**
     * Creates an Issue from raw data
     * @param array $issue
     * @return Issue
     */
    function toIssue(array $issue);

    /**
     * Creates a NewIssue from raw data
     * @param array $issue
     * @return NewIssue
     */
    function toNewIssue(array $issue);

    /**
     * Converts an NewIssue to raw data
     * @param NewIssue $issue
     * @return array raw data
     */
    function issueToArray(NewIssue $issue);

    /**
     * Creates a Comment from raw data
     * @param array $comment
     * @return Comment
     */
    function toComment(array $comment);

    /**
     * Converts a NewComment to raw data 
     * @param NewComment $comment
     * @return array
     */
    function commentToArray(NewComment $comment);
}
