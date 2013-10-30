<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\NewIssue;
use Qissues\Model\Number;
use Qissues\Model\Status;
use Qissues\Model\User;
use Qissues\Model\NewComment;
use Qissues\Model\SearchCriteria;
use Qissues\Model\IssueTracker;
use Guzzle\Http\Client;

class GitHubTracker implements IssueTracker
{
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
    public function lookup(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue));
        $data = $request->send()->json();
        return GitHubConverter::toIssue($data);
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $request = $this->request('GET', sprintf('/repos/%s/issues', $this->config['repository']));
        $response = $request->send()->json();
        return array_map(__NAMESPACE__ . '\\GitHubConverter::toIssue', $response);
    }

    /**
     * {@inheritDoc}
     */
    public function findComments(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue, '/comments'));
        $response = $request->send()->json();
        return array_map(__NAMESPACE__ . '\\GitHubConverter::toComment', $response);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(NewIssue $issue)
    {
        $request = $this->request('POST', sprintf('/repos/%s/issues', $this->config['repository']));
        $request->setBody(json_encode(GitHubConverter::toArray($issue)), 'application/json');
        $response = $request->send()->json();
        return new Number($body['number']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->config['repository'], $issue['id']));
        $request->setBody(json_encode(GitHubConverter::toArray($issue)), 'application/json');
        $response = $request->send();
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
