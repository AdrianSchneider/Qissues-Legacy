<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Workflow\BasicTransitioner;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Trackers\Shared\FieldMapping;
use Qissues\Trackers\Shared\Metadata\NullMetadata;
use Guzzle\Http\Client;

class GitHubRepository implements IssueRepository, BasicTransitioner
{
    protected $repository;
    protected $username;
    protected $password;
    protected $mapping;
    protected $client;

    /**
     * @param string $repository
     * @param string username
     * @param string password
     * @param IssueTracker $tracker
     * @param Client|null $client to override
     */
    public function __construct($repository, $username, $password, FieldMapping $mapping, Client $client = null)
    {
        $this->repository = $repository;
        $this->username = $username;
        $this->password = $password;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client('https://api.github.com/', array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://github.com/%s/issues', $this->repository);
    }

    /**
     * {@inheritDoc}
     */
    public function lookup(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue));
        $data = $request->send()->json();
        return $this->mapping->toIssue($data);
    }

    /**
     * {@inheritDoc}
     */
    public function lookupUrl(Number $issue)
    {
        return sprintf('https://github.com/%s/issues/%d', $this->repository, (string)$issue);
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $request = $this->request('GET', sprintf('/repos/%s/issues', $this->repository));
        foreach ($this->mapping->buildSearchQuery($criteria) as $key => $value) {
            $request->getQuery()->set($key, $value);
        }

        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toIssue'), $response);
    }

    /**
     * {@inheritDoc}
     */
    public function findComments(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue, '/comments'));
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toComment'), $response);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(NewIssue $issue)
    {
        $request = $this->request('POST', sprintf('/repos/%s/issues', $this->repository));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $response = $request->send()->json();
        return new Number($response['number']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->repository, $number->getNumber()));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, Message $comment)
    {
        $request = $this->request('POST', $this->getIssueUrl($issue, '/comments'));
        $request->setBody(json_encode(array('body' => $comment->getMessage())), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Number $issue)
    {
        throw new \DomainException('GitHub cannot delete issues');
    }

    /**
     * {@inheritDoc}
     */
    public function changeStatus(Number $issue, Status $status)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->repository, $issue->getNumber()));
        $request->setBody(json_encode(array('state' => $status->getStatus())), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->repository, $issue->getNumber()));
        $request->setBody(json_encode(array('assignee' => $user->getAccount())), 'application/json');
        $request->send();
    }

    /**
     * Prepare an authenticated HTTP request
     *
     * @param string $method (GET, POST, etc.)
     * @param string $url
     * @return Request
     */
    protected function request($method, $url)
    {
        $request = call_user_func(array($this->client, $method), $url);
        $request->setAuth($this->username, $this->password);
        return $request;
    }

    /**
     * Prepare the URL for an issue
     *
     * @param Number $number issue num
     * @param string $append to URL (ex: '/comments')
     * @return string url
     */
    protected function getIssueUrl(Number $number, $append = '')
    {
        return sprintf('/repos/%s/issues/%d%s', $this->repository, $number->getNumber(), $append);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata()
    {
        throw new \DomainException('No metadata necessary for GitHub');
    }
}
