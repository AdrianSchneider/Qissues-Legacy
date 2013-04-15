<?php

namespace Qissues\Connector;

class Jira implements Connector
{
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(array $issue)
    {

    }

    public function update(array $changes, array $issue)
    {

    }

    public function changeStatus(array $issue, $newStatus)
    {

    }

    public function assign(array $issue, $username)
    {

    }

    public function find($id)
    {
        $url = sprintf(
            'https://%s:%s@%s.atlassian.net/rest/api/2/issue/%s',
            $this->config['username'],
            $this->config['password'],
            urlencode($this->config['project']),
            $this->config['prefix'] . '-' . $id
        );

        if (!$issue = json_decode(file_get_contents($url), true)) {
            return;
        }

        return $this->prepareIssue($issue);
    }

    public function findAll(array $options = array())
    {
        $url = sprintf(
            'https://%s:%s@%s.atlassian.net/rest/api/2/search?jql=%s',
            $this->config['username'],
            $this->config['password'],
            urlencode($this->config['project']),
            urlencode($this->generateJql($options))
        );

        $issues = json_decode(file_get_contents($url), true);
        return array_map(array($this, 'prepareIssue'), $issues['issues']);
    }

    protected function generateJql(array $options)
    {
        $where = array('project = "' . $this->config['project'] . '"');

        if ($options['assignee']) {
            $where[] = 'assignee = "' . $options['assignee'] . '"';
        }

        // XXX
        if ($options['status'] == 'new,open') {
            $where[] = 'resolution = Unresolved';
        }

        $sort = array();
        if (!empty($options['sort'])) {
            if ($options['sort'] == 'priority') {
                $sort[] = 'priority DESC';
            }
        } else {
            $sort[] = 'updatedDate DESC';
        }

        return sprintf(
            '%s ORDER BY %s',
            implode(' AND ', $where),
            implode(', ', $sort)
        );
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
            'comments'      => 0
        );
    }

    public function findComments(array $issue)
    {
        // TODO
        return array();
    }

    public function comment(array $issue, $message)
    {

    }
}
