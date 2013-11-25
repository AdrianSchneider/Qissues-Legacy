<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Tracker\IssueRepository;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Tracker\Metadata\NullMetadata;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\ClosedStatus;
use Qissues\Model\Meta\User;
use Guzzle\Http\Client;
use Guzzle\Http\QueryAggregator\DuplicateAggregator;

class JiraRepository implements IssueRepository
{
    protected $repository;
    protected $username;
    protected $password;
    protected $mapping;
    protected $client;

    /**
     * @param string $project ex "project"(.atlassian.net)
     * @param string $prefix ex "PROJ"
     * @param string username
     * @param string password
     * @param IssueTracker $tracker
     * @param Client|null $client to override
     */
    public function __construct($project, $prefix, $username, $password, FieldMapping $mapping, Client $client = null)
    {
        $this->project = $project;
        $this->prefix = $prefix;
        $this->username = $username;
        $this->password = $password;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client( sprintf('https://%s.atlassian.net/', $this->project), array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://%s.atlassian.net/issues', $this->project);
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
        return sprintf(
            'https://%s.atlassian.net/browse/%s-%d',
            $this->project,
            $this->prefix,
            $issue->getNumber()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $request = $this->request('GET', "/search");
        $request->getQuery()->merge($this->mapping->buildSearchQuery($criteria));
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toIssue'), $response['issues']);
    }

    /**
     * {@inheritDoc}
     */
    public function findComments(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue, '/comment'));
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toComment'), $response['comments']);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(NewIssue $issue)
    {
        $request = $this->request('POST', "/issue");
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $response = $request->send()->json();
        list($key, $id) = explode('-', $response['key']);
        return new Number($id);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        throw new \Exception('not yet implemented');
        $request = $this->request('PUT', $this->getIssueUrl($number));
        $request->setBody($this->mapping->issueToArray($issue));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, NewComment $comment)
    {
        $request = $this->request('POST', $this->getIssueUrl($issue, '/comment'));
        $request->setBody(json_encode(array('body' => $comment->getMessage())), 'application/json');
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
     * JIRA's statuses are much more complex and involve transitions
     * or a workflow model to be introduced before we can effectively
     * support it.
     *
     * For example,
     * start -> in progress (require assignee)
     * resolve -> resolved (require resolution)
     *
     * These are entirely dynamic and customizable by the PM.
     *
     * {@inheritDoc}
     * @throws DomainException
     */
    public function changeStatus(Number $issue, Status $status)
    {
        throw new \DomainException('Status changes coming in a later version when workflows are better modeled');
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        $request = $this->request('PUT', $this->getIssueUrl($issue, "/assignee"));
        $request->setBody(json_encode(array('name' => $user->getAccount())), 'application/json');
        $response = $request->send()->json();
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
        $url = 'rest/api/2' . $url;
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
        return sprintf(
            '/issue/%s-%d%s',
            $this->prefix,
            $number->getNumber(),
            $append
        );
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata()
    {
        $request = $this->request('GET', '/issue/createmeta');
        $response = $request->send()->json();

        foreach ($response['projects'] as $project) {
            if ($project['key'] != $this->prefix) {
                continue;
            }

            $metadata = array(
                'id' => $project['id'],
                'key' => $this->prefix,
                'types' => array(),
                'components' => array(),
                'statuses' => array()
            );

            $request = $this->request('GET', "/project/$project[key]");
            $request->getQuery()->set('expand', 'projectKeys');
            $response = $request->send()->json();

            $tasks = array();
            foreach ($response['issueTypes'] as $type) {
                $metadata['types'][$type['id']] = array(
                    'id' => $type['id'],
                    'name' => $type['name']
                );
            }

            $request = $this->request('GET', "/project/$project[key]/statuses");
            $response = $request->send()->json();

            foreach ($response as $type) {
                $metadata['types'][$type['id']]['statuses'] = array();
                foreach ($type['statuses'] as $status) {
                    $metadata['types'][$type['id']]['statuses'][] = array(
                        'id' => $status['id'],
                        'name' => $status['name']
                    );
                }
            }

            return $metadata;
        }

        throw new \Exception('Could not find project with matching key');
    }
}
