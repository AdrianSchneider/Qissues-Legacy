<?php

namespace Qissues\Trackers\InMemory;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Shared\Status;
use Qissues\Application\Tracker\BasicTransitioner;
use Qissues\Domain\Shared\User;

class InMemoryRepository implements IssueRepository, BasicTransitioner
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
        $this->issues[$index]['comments'] = array();

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
        return array_map(
            array($this->mapping, 'toComment'),
            $this->issues[(string)$issue]['comments']
        );
    }

    /**
     * {@inheritDoc}
     */
    function comment(Number $issue, Message $comment)
    {
        $index = (string)$issue;
        $this->issues[$index]['comments'][] = array(
            'message' => (string)$comment
        );
    }

    public function changeStatus(Number $number, Status $status, array $details = array())
    {
        $index = (string)$number;
        $this->issues[$index]['status'] = $status->getStatus();
        $this->issues[$index]['transitionDetails'] = $details;
    }

    public function getStatusDetails(Number $number)
    {
        $index = (string)$number;
        return $this->issues[$index]['transitionDetails'];
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
