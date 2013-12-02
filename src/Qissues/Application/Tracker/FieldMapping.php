<?php

namespace Qissues\Application\Tracker;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\SearchCriteria;

interface FieldMapping
{
    /**
     * Prepare fields for editing
     * @param Issue|null $issue, if existing
     * @return ExpectedDetails
     */
    function getExpectedDetails(Issue $issue = null);

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
     * Maps the search query to the http query fields
     * @param SearchCriteria $criteria
     * @return array fields
     */
    function buildSearchQuery(SearchCriteria $criteria);
}
