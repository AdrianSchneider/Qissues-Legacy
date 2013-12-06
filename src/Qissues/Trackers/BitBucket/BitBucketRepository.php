<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\ClosedStatus;
use Qissues\Application\Tracker\BasicTransitioner;
use Qissues\Application\Tracker\FieldMapping;
use Qissues\Application\Tracker\Metadata\NullMetadata;
use Guzzle\Http\Client;
use Guzzle\Http\QueryAggregator\DuplicateAggregator;

class BitBucketRepository implements IssueRepository, BasicTransitioner
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
        $this->repository = strtolower($repository);
        $this->username = $username;
        $this->password = $password;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client('https://api.bitbucket.org/', array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://bitbucket.org/%s/issues', $this->repository);
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
        return sprintf('https://bitbucket.org/%s/issue/%d', $this->repository, (string)$issue);
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $request = $this->request('GET', sprintf('/repositories/%s/issues', $this->repository));
        $request->getQuery()->setAggregator(new DuplicateAggregator());
        $request->getQuery()->overwriteWith($this->mapping->buildSearchQuery($criteria));

        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toIssue'), $response['issues']);
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
        $request = $this->request('POST', sprintf('/repositories/%s/issues', $this->repository));
        $request->setBody($this->mapping->issueToArray($issue));
        $response = $request->send()->json();
        return new Number($response['local_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        $request = $this->request('PUT', $this->getIssueUrl($number));
        $request->setBody($this->mapping->issueToArray($issue));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, Message $comment)
    {
        $request = $this->request('POST', $this->getIssueUrl($issue, '/comments'));
        $request->setBody(array('content' => $comment->getMessage()));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Number $issue)
    {
        $request = $this->request('DELETE', $this->getIssueUrl($issue));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function changeStatus(Number $issue, Status $status)
    {
        if ($status instanceof ClosedStatus) {
            $status = new Status('resolved');
        }

        $status = $this->mapping->getStatusMatching($status);

        $request = $this->request('PUT', $this->getIssueUrl($issue));
        $request->setBody(array('status' => $status->getStatus()));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        if ($user instanceof CurrentUser) {
            $user = new User($this->username);
        }

        $request = $this->request('PUT', $this->getIssueUrl($issue));
        $request->setBody(array('responsible' => $user->getAccount()));
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
        $url = '1.0' . $url;
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
        return sprintf('/repositories/%s/issues/%d%s', $this->repository, $number->getNumber(), $append);
    }

    public function fetchMetadata()
    {
        $data = array();

        $request = $this->request('GET', sprintf('/repositories/%s/issues/components', $this->repository));
        $response = $request->send()->json();

        $data['components'] = $response;
        return $data;
    }
}
