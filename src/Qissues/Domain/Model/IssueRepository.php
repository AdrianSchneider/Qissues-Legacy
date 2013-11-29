<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Shared\User;

interface IssueRepository
{
    /**
     * Get the issues URL
     * @return string url
     */
    function getUrl();

    /**
     * Create a new Issue
     * @param NewIssue $issue
     * @return Number
     */
    function persist(NewIssue $issue);

    /**
     * Update an existing Issue
     * @param NewIssue $issue
     * @param Number $num
     */
    function update(NewIssue $issue, Number $num);

    /**
     * Delete an existing Issue
     * @param Number $num (issue number)
     */
    function delete(Number $num);

    /**
     * Assign an Issue to User
     * @param Number $issue
     * @param User $user
     */
    function assign(Number $issue, User $user);

    /**
     * Find an Issue by Number
     * @param Number $issue
     * @return Issue
     */
    function lookup(Number $issue);

    /**
     * Find an issue online by Number
     * @param Number $issue
     * @return string url
     */
    function lookupUrl(Number $issue);

    /**
     * Find issues matching Criteria
     * @param SearchCriteria $criteria
     */
    function query(SearchCriteria $criteria);

    /**
     * Find comments for an Issue
     * @param Number $number
     * @return Comment[]
     */
    function findComments(Number $issue);

    /**
     * Comment on an Issue
     * @param Number $issue
     * @param NewComment $comment
     */
    function comment(Number $issue, NewComment $comment);

    /**
     * Retrieve the metadata required for mapping
     * @return array
     */
    function fetchMetadata();
}
