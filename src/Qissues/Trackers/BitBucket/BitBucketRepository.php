<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Model\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Tracker\IssueRepository;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\User;
use Guzzle\Http\Client;

class BitBucketRepository implements IssueRepository
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
        return sprintf('https:/bitbucket.org/%s/issue/%d', $this->repository, (string)$issue);
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $request = $this->request('GET', sprintf('/repositories/%s/issues', $this->repository));
        foreach ($this->convertCriteriaToQuery($criteria) as $key => $value) {
            $request->getQuery()->set($key, $value);
        }

        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toIssue'), $response['issues']);
    }

    /**
     * Converts a SearchCriteria object into querystring pairs
     * @param SearchCriteria $criteria
     * @return array query
     */
    protected function convertCriteriaToQuery(SearchCriteria $criteria)
    {
        return array();
        $query = array();

        if ($sortFields = $criteria->getSortFields()) {
            $validFields = array('created', 'updated', 'comments');

            if (count($sortFields) > 1) {
                throw new \DomainException('BitBucket cannot multi-sort');
            }
            if (!in_array($sortFields[0], $validFields)) {
                throw new \DomainException("Sorting by '$sortFields[0]' is unsupported on BitBucket");
            }

            $query['sort'] = $sortFields[0];
        }

        if ($statuses = $criteria->getStatuses()) {
            if (count($statuses) > 1) {
                throw new \DomainException('BitBucket cannot support multiple statuses');
            }

            $query['state'] = $statuses[0]->getStatus();
        }

        if ($labels = $criteria->getLabels()) {
            $query['labels'] = implode(',', array_map('strval', $labels));
        }

        return $query;
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
    public function comment(Number $issue, NewComment $comment)
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
        throw new \Exception('not yet implemented');
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
}