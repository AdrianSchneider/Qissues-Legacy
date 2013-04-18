<?php

namespace Qissues\Connector;

use Guzzle\Http\Client;

class Jira implements Connector
{
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client( sprintf('https://%s.atlassian.net/', $this->config['project']), array(
            'ssl.certificate_authority' => 'system'
        ));
    }

    public function create(array $issue)
    {
        throw new \Exception('not yet implemented');
    }

    public function update(array $changes, array $issue)
    {
        throw new \Exception('not yet implemented');
    }

    public function delete(array $issue)
    {
        throw new \Exception('not yet implemented');
    }

    public function changeStatus(array $issue, $newStatus)
    {
        $request = $this->request('put', sprintf('/issue/%s-%d', $this->config['prefix'], $issue['id']));
        $request->setBody(json_encode(array('fields' => array('status' => $newstatus))), 'application/json');
        $request->send();
    }

    public function assign(array $issue, $username)
    {
        $request = $this->request('put', sprintf('/issue/%s-%d/assignee', $this->config['prefix'], $issue['id']));
        $request->setBody(json_encode(array('name' => $username)), 'application/json');
        $request->send();
    }

    public function find($id)
    {
        $request = $this->request('get', sprintf('/issue/%s-%d', $this->config['prefix'], $id));
        $issue = $request->send()->json();
        return $this->prepareIssue($issue);
    }

    public function findAll(array $options = array())
    {
        $request = $this->request('get', '/search?jql=' . $this->generateJql($options));
        $response = $request->send()->json();
        return array_map(array($this, 'prepareIssue'), $response['issues']);
    }

    /**
     * Generates the JQL given our options
     *
     * @param array options from input
     * @return string urlencoded JQL
     */
    protected function generateJql(array $options)
    {
        $where = array('project = "' . $this->config['project'] . '"');

        $quote = function($text) { return sprintf('"%s"', addslashes($text)); };

        if (!empty($options['assignee'])) {
            $where[] = 'assignee IN (' . implode(',', array_map($quote, $options['assignee'])) . ')';
        }
        if (!empty($options['type'])) {
            $where[] = 'issuetype IN (' . implode(',', array_map($quote, $options['type'])) . ')';
        }

        if (!empty($options['status'])) {
            if (in_array('open', $options['status'])) {
                $where[] = 'resolution = Unresolved';
                $options['status'] = array_diff($options['status'], array('open'));
            }
            if (!empty($options['status'])) {
                $where[] = 'status IN (' . implode(',', array_map($quote, $options['status'])) . ')';
            }
        }

        $sortMapping = array(
            'priority' => 'priority DESC',
            'updated' => 'updatedDate DESC',
            'created' => 'createdDate DESC'
        );
        $sort = array();
        foreach ($options['sort'] as $by) {
            if (isset($sortMapping[$by])) {
                $sort[] = $sortMapping[$by];
            }
        }

        return urlencode(sprintf(
            '%s ORDER BY %s',
            implode(' AND ', $where),
            implode(', ', $sort)
        ));
    }

    /**
     * Format an incoming issue
     *
     * @param array issue from jira
     * @return array application-ready issue
     */
    protected function prepareIssue(array $issue)
    {
        return array(
            'id'            => substr($issue['key'], strpos($issue['key'], '-') + 1),
            'title'         => $issue['fields']['summary'],
            'description'   => $issue['fields']['description'],
            'assignee'      => $issue['fields']['assignee']['name'],
            'created'       => new \DateTime($issue['fields']['created']),
            'updated'       => new \DateTime($issue['fields']['updated']),
            'status'        => strtolower($issue['fields']['status']['name']),
            'priority'      => $issue['fields']['priority']['id'],
            'priority_text' => strtolower($issue['fields']['priority']['name']),
            'type'          => strtolower($issue['fields']['issuetype']['name']),
            'comments'      => 'n/a'
        );
    }

    protected function prepareComment(array $comment)
    {
        return array(
            'username' => $comment['author']['name'],
            'message'  => $comment['body'],
            'date'     => new \DateTime($comment['created'])
        );
    }


    public function findComments(array $issue)
    {
        $request = $this->client->get(sprintf('/rest/api/2/issue/%s-%d/comment', $this->config['prefix'], $issue['id']));
        $request->setAuth($this->config['username'], $this->config['password']);

        $response = $request->send()->json();
        return array_map(array($this, 'prepareComment'), $response['comments']);
    }

    public function comment(array $issue, $message)
    {
        $request = $this->request('POST', sprintf('/issue/%s-%d/comment', $this->config['prefix'], $issue['id']));
        $request->setBody(json_encode(array('body' => $message)), 'application/json');
        $request->send();
    }

    public function getBrowseUrl()
    {
        return sprintf(
            'https://%s.atlassian.net/issues',
            $this->config['project']
        );
    }

    public function getIssueUrl(array $issue)
    {
        return sprintf(
            'https://%s.atlassian.net/browse/%s-%d',
            $this->config['project'],
            $this->config['prefix'],
            $issue['id']
        );
    }

    /**
     * Generate an authenticated request
     *
     * @param string HTTP method
     * @param string URL 
     * @return Request
     */
    protected function request($method, $url)
    {
        if (strpos($url, 'http') === false) {
            $url = sprintf('https://%s.atlassian.net/rest/api/2%s', $this->config['project'], $url);
        }

        $request = call_user_func(array($this->client, $method), $url);
        $request->setAuth($this->config['username'], $this->config['password']);

        return $request;
    }
}
