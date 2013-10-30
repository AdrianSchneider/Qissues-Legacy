<?php

namespace Qissues\Trackers;

use Qissues\Model\IssueTracker;
use Guzzle\Http\Client;

class GitHub implements IssueTracker
{
    /**
     * @var array connector configuration
     */
    protected $config;

    /**
     * @var Client guzzle http client
     */
    protected $client;

    /**
     * @param array $config connector config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client('https://api.github.com/', array(
            'ssl.certificate_authority' => 'system'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getEditorFields()
    {
        return array(
            'title' => 'title',
            'milestone' => 'milestone',
            'assignee' => 'assignee'
        );
    }

    /**
     * Creates a new issue
     *
     * @param array $issue 
     * @return array created issue from GitHub
     */
    public function create(array $issue)
    {
        $post = array(
            'title' => $issue['title'],
            'body'  => $issue['description']
        );

        if (!empty($issue['labels'])) {
            $post['labels'] = $issue['labels'];
        }
        if (!empty($issue['milestone'])) {
            $post['milestone'] = $issue['milestone'];
        }
        if (!empty($issue['assignee'])) {
            $post['assignee'] = $issue['assignee'];
        }

        $request = $this->request('POST', sprintf('/repos/%s/issues', $this->config['repository']));
        $request->setBody(json_encode($post), 'application/json');

        $response = $request->send();
        $body = $response->json();

        $issue['id'] = $body['number'];
        return $issue;
    }

    public function update(array $changes, array $issue)
    {
        $post = array(
            'title' => $issue['title'],
            'body'  => $issue['description']
        );

        if (!empty($issue['labels'])) {
            $post['labels'] = $issue['labels'];
        }
        if (!empty($issue['milestone'])) {
            $post['milestone'] = $issue['milestone'];
        }
        if (!empty($issue['assignee'])) {
            $post['assignee'] = $issue['assignee'];
        }

        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->config['repository'], $issue['id']));
        $request->setBody(json_encode($post), 'application/json');
        $response = $request->send();

        return $issue;
    }

    public function delete(array $issue)
    {
        throw new \Exception('not yet implemented');
    }

    public function changeStatus(array $issue, $newStatus)
    {
        throw new \Exception('not yet implemented');
    }

    public function assign(array $issue, $username)
    {
        throw new \Exception('not yet implemented');
    }

    /**
     * Find an issue by ID
     * @param integer $id
     * @return array Issue
     */
    public function find($id)
    {
        $request = $this->request('GET', '/repos/' . $this->config['repository'] . '/issues/' . $id);
        $issue = $request->send()->json();
        return $this->prepareIssue($issue);
    }

    /**
     * Prepare an Issue for application use
     * @param array $issue
     * @return array issue
     */
    protected function prepareIssue(array $issue)
    {
        return array(
            'id'            => $issue['number'],
            'title'         => $issue['title'],
            'description'   => $issue['body'],
            'assignee'      => $issue['assignee'] ? $issue['assignee']['login'] : '',
            'created'       => new \DateTime($issue['created_at']),
            'updated'       => new \DateTime($issue['updated_at']),
            'status'        => $issue['state'],
            'priority'      => 1,
            'priority_text' => 'n/a',
            'type'          => 'TODO',
            'comments'      => $issue['comments']
        );
    }

    /**
     * Prepare a Comment for application use
     * @param array $comment from GitHub
     * @return array comment
     */
    protected function prepareComment(array $comment)
    {
        return array(
            'username' => $comment['user']['login'],
            'message'  => $comment['body'],
            'date'     => new \DateTime($comment['created_at'])
        );
    }

    /**
     * Find all issues
     * @param array $options criteria, sorting, etc.
     * @return array issues
     */
    public function findAll(array $options)
    {
        $request = $this->request('GET', sprintf('/repos/%s/issues', $this->config['repository']));
        $response = $request->send()->json();

        return array_map(array($this, 'prepareIssue'), $response);
    }

    /**
     * Find comments for a given issue
     * @param array $issue
     * @return array comments
     */
    public function findComments(array $issue)
    {
        $request = $this->request('GET', sprintf('/repos/%s/issues/%d/comments', $this->config['repository'], $issue['id']));
        $response = $request->send()->json();

        return array_map(array($this, 'prepareComment'), $response);
    }

    /**
     * Create a comment on Issue
     * @param array $issue
     * @param string $message
     */
    public function comment(array $issue, $message)
    {
        $request = $this->request('POST', sprintf('/repos/%s/issues/%d/comments', $this->config['repository'], $issue['id']));
        $request->setBody(json_encode(array('body' => $message)), 'application/json');
        $response = $request->send()->json();
    }

    /**
     * Prepare an authenticated HTTP request
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
}
