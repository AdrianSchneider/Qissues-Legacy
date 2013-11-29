<?php

namespace Qissues\Trackers\InMemory;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Request\NewComment;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\IssueRepository;

class InMemoryRepository implements IssueRepository
{
    protected $issues;
    protected $index;
    protected $mapping;

    public function __construct(array $issues = array())
    {
        $this->index = 0;
        $this->mapping = new InMemoryMapping($this);
        $this->issues = array();

        foreach ($issues as $issue) {
            $this->persist($this->mapping->toNewIssue($issue));
        }
    }

    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * {@inheritDoc}
     */
    function getUrl()
    {
        throw new \DomainException('testing only');
    }

    /**
     * {@inheritDoc}
     */
    function persist(NewIssue $issue)
    {
        $index = ++$this->index;
        $this->issues[$index] = array('number' => $index) + $this->mapping->issueToArray($issue);
        $this->issues[$index]['status'] = 'new';
        $this->issues[$index]['created'] = date('Y-m-d');
        $this->issues[$index]['updated'] = date('Y-m-d');

        return new Number($index);
    }

    /**
     * {@inheritDoc}
     */
    function update(NewIssue $issue, Number $num)
    {
        $index = (string)$num;
        $this->issues[$index] = $this->mapping->issueToArray($issue) + $this->issues[$index];
    }

    /**
     * {@inheritDoc}
     */
    function delete(Number $num)
    {
        unset($this->issues[(string)$num]);
    }

    /**
     * {@inheritDoc}
     */
    function assign(Number $issue, User $user)
    {
        $index = (string)$issue;
        $this->issues[$index]['assignee'] = $user->getAccount();
    }

    /**
     * {@inheritDoc}
     */
    function lookup(Number $issue)
    {
        if (!empty($this->issues[$issue->getNumber()])) {
            return $this->mapping->toIssue($this->issues[$issue->getNumber()]);
        }
    }

    /**
     * {@inheritDoc}
     */
    function lookupUrl(Number $issue)
    {
        throw new \DomainException('Cannot get url, just testing');
    }

    /**
     * {@inheritDoc}
     */
    function query(SearchCriteria $criteria)
    {
        return array_map(array($this->mapping, 'toIssue'), $this->issues);
    }

    /**
     * {@inheritDoc}
     */
    function findComments(Number $issue)
    {

    }

    /**
     * {@inheritDoc}
     */
    function comment(Number $issue, NewComment $comment)
    {

    }

    /**
     * {@inheritDoc}
     */
    function fetchMetadata()
    {

    }

    public function getMapping()
    {
        return $this->mapping;
    }
}
