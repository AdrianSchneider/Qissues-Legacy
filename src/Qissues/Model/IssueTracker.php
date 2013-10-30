<?php

namespace Qissues\Model;

use Qissues\Model\Issue;
use Qissues\Model\NewIssue;
use Qissues\Model\Status;
use Qissues\Model\Number;
use Qissues\Model\User;
use Qissues\Model\SearchCriteria;

interface IssueTracker
{
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
     * Change an issue's status
     * @param Number $issue number
     * @param Status new status
     */
    function changeStatus(Number $issue, Status $status);

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

    function getIssueConverter();
    function getCommentConverter();
}
