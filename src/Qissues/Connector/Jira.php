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

    }

    public function findAll(array $options = array())
    {
        $url = sprintf(
            'https://%s.atlassian.net/rest/api/2/search?%s',
            $this->config['project'],
            'resolution = Unresolved ORDER BY priority DESC, updatedDate DESC'
        );

        $issues = json_decode(file_get_contents($url), true);
        var_dump($issues) ;exit;
    }

    public function findComments(array $issue)
    {

    }

    public function comment(array $issue, $message)
    {

    }
}
