<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Application\Tracker\FieldMapping;
use Qissues\Domain\Workflow\Transition;
use Guzzle\Http\Client;

class JiraRepository implements IssueRepository
{
    protected $repository;
    protected $username;
    protected $password;
    protected $mapping;
    protected $client;

    /**
     * @param string $host
     * @param string $projectKey (or issue prefix)
     * @param string username
     * @param string password
     * @param IssueTracker $tracker
     * @param Client|null $client to override
     */
    public function __construct($host, $projectKey, $username, $password, FieldMapping $mapping, Client $client = null)
    {
        $this->host = $host;
        $this->projectKey = $projectKey;
        $this->username = $username;
        $this->password = $password;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client( sprintf('https://%s/', $this->host), array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://%s/issues', $this->host);
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
            'https://%s/browse/%s-%d',
            $this->host,
            $this->projectKey,
            $issue->getNumber()
        );
    }

    /**
     * Grab available transitions for an Issue
     * @param Number $issue
     * @return array
     */
    public function lookupTransitions(Number $issue)
    {
        static $transitions = array();
        $id = $issue->getNumber();

        if (isset($transitions[$id])) {
            return $transitions[$id];
        }

        $request = $this->request('GET', $this->getIssueUrl($issue, "/transitions"));
        $request->getQuery()->set('expand', 'transitions.fields');
        $response = $request->send()->json();

        return $transitions[$id] = $response['transitions'];
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
        $request = $this->request('PUT', $this->getIssueUrl($number));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, Message $comment)
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
     * Apply a transition to an Issue
     *
     * @param Transition $transition
     */
    public function changeStatus(Number $issue, Status $status, $transitionId, $fields)
    {
        $payload = array(
            'transition' => array('id' => $transitionId),
            'fields' => $fields->getDetails()
        );

        if (!empty($payload['fields']['resolution'])) {
            $payload['fields']['resolution'] = array('name' => $payload['fields']['resolution']);
        }

        if (empty($payload['fields'])) {
            unset($payload['fields']);
        }

        $request = $this->request('POST', $this->getIssueUrl($issue, "/transitions"));
        $request->setBody(json_encode($payload), 'application/json');
        $response = $request->send()->json();
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        if ($user instanceof CurrentUser) {
            $user = new User($this->username);
        }

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
            $this->projectKey,
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
            if ($project['key'] != $this->projectKey) {
                continue;
            }

            $metadata = array(
                'id' => $project['id'],
                'key' => $this->projectKey,
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

            $request = $this->request('GET', "/project/$project[key]/components");
            $response = $request->send()->json();

            foreach ($response as $component) {
                $metadata['components'][] = array(
                    'id' => $component['id'],
                    'name' => $component['name']
                );
            }

            return $metadata;
        }

        throw new \Exception('Could not find project with matching key');
    }
}
