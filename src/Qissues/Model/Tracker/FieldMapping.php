<?php

namespace Qissues\Model\Tracker;

use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;

interface FieldMapping
{
    function map($dtoField);
    function reverseMap($issueField);

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
     * Creates a NewComment from raw data
     * @param array $comment
     * @return NewComment
     */
    function toNewComment(array $cooment);

    /**
     * Converts a NewComment to raw data 
     * @param NewComment $comment
     * @return array
     */
    function commentToArray(NewComment $comment);
}
