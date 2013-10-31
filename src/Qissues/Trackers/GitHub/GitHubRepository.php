<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Tracker\IssueRepository;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\User;
use Guzzle\Http\Client;

class GitHubRepository implements IssueRepository
{
    /**
     * @param array $config connector config
     * @param IssueTracker $tracker
     * @param Client|null $client to override
     */
    public function __construct(array $config, FieldMapping $mapping, Client $client = null)
    {
        $this->config  = $config;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client('https://api.github.com/', array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://github.com/%s/issues', $this->config['repository']);
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
        return sprintf('https://github.com/%s/issues/%d', $this->config['repository'], (string)$issue);
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $request = $this->request('GET', sprintf('/repos/%s/issues', $this->config['repository']));
        foreach ($this->convertCriteriaToQuery($criteria) as $key => $value) {
            $request->getQuery()->set($key, $value);
        }

        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toIssue'), $response);
    }

    /**
     * Converts a SearchCriteria object into querystring pairs
     * @param SearchCriteria $criteria
     * @return array query
     */
    protected function convertCriteriaToQuery(SearchCriteria $criteria)
    {
        $query = array();

        if ($statuses = $criteria->getStatuses()) {
            if (count($statuses) > 1) {
                throw new \DomainException('GitHub cannot support multiple statuses');
            }

            $query['state'] = $statuses[0]->getStatus();
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
        $request = $this->request('POST', sprintf('/repos/%s/issues', $this->config['repository']));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $response = $request->send()->json();
        return new Number($response['number']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->config['repository'], $issue['id']));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, NewComment $comment)
    {
        $request = $this->request('POST', $this->getIssueUrl($issue, '/comments'));
        $request->setBody(json_encode(array('body' => $message)), 'application/json');
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
        throw new \Exception('not yet implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        throw new \Exception('not yet implemented');
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
        $request->setAuth($this->config['username'], $this->config['password']);
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
        return sprintf('/repos/%s/issues/%d%s', $this->config['repository'], $number->getNumber(), $append);
    }
}