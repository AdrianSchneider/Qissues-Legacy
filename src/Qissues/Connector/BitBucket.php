<?php

namespace Qissues\Connector;

use Guzzle\Http\Client;

class BitBucket implements Connector
{
    protected $priorities = array(
        'blocker'  => 5,
        'critical' => 4,
        'major'    => 3,
        'minor'    => 2,
        'trivial'  => 1
    );

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client('https://api.bitbucket.org/', array(
            'ssl.certificate_authority' => 'system'
        ));
    }

    /**
     * Creates a new Issue
     *
     * @param array issue details
     * @return array created issue from BitBucket
     */
    public function create(array $issue)
    {
        $post = array(
            'title'       => $issue['title'],
            'priority'    => $issue['priority'],
            'content'     => $issue['description'],
            'kind'        => $issue['type'],
            'responsible' => $issue['assignee']
        );

        $url = sprintf(
            'https://api.bitbucket.org/1.0/repositories/%s/issues',
            $this->config['repository']
        );

        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $url);
        curl_setopt($ch, \CURLOPT_POST, true);
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, \CURLOPT_USERPWD, sprintf('%s:%s', $this->config['username'], $this->config['password']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($error = curl_error($ch)) {
            throw new \Exception($error);
        }

        $issue = json_decode($result, true);
        return $this->prepareIssue($issue);
    }

    /**
     * Edit an existing issue
     *
     * @param array changes
     * @param array existing issue
     */
    public function update(array $changes, array $issue)
    {
        $this->changeFields($issue, array(
            'title'       => $changes['title'],
            'priority'    => $changes['priority'],
            'content'     => $changes['description'],
            'kind'        => $changes['type'],
            'responsible' => $changes['assignee']
        ));
    }

    /**
     * Deletes an issue
     *
     * @param array issue
     */
    public function delete(array $issue)
    {
        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $issue['url_endpoint']);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, \CURLOPT_USERPWD, sprintf('%s:%s', $this->config['username'], $this->config['password']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($error = curl_error($ch)) {
            throw new \Exception($error);
        }
    }

    /**
     * Change the status of an issue
     *
     * @param array issue
     * @param string new status
     */
    public function changeStatus(array $issue, $status)
    {
        $this->changeFields($issue, array('status' => $status));
    }

    /**
     * Change arbitrary fields
     *
     * @param array issue
     * @param array changes
     */
    protected function changeFields(array $issue, array $changes)
    {
        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $issue['url_endpoint']);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, \CURLOPT_POSTFIELDS, http_build_query($changes));
        curl_setopt($ch, \CURLOPT_USERPWD, sprintf('%s:%s', $this->config['username'], $this->config['password']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($error = curl_error($ch)) {
            throw new \Exception($error);
        }
    }

    /**
     * (Re-)assign the issue
     *
     * @param array issue
     * @param string username
     */
    public function assign(array $issue, $username)
    {
        $this->changeFields($issue, array('responsible' => $username));
    }

    /**
     * Find an issue by its ID
     *
     * @param integer ID
     * @return array issue details
     */
    public function find($id)
    {
        $request = $this->request('get', sprintf('/issues/%d', $id));
        $issue = $request->send()->json();
        return $this->prepareIssue($issue);
    }

    /**
     * Query issues
     *
     * @param array filters/options
     * @return array issues
     */
    public function findAll(array $options = array())
    {
        $request = $this->request('get', '/issues');
        $request->getQuery()->merge(array(
            'limit' => $options['limit'],
            'status' => $options['status'],
            'kind' => $options['type']
        ));

        $response = $request->send()->json();
        var_dump($response); exit;
        return array_map(array($this, 'prepareIssue'), $response['issues']);


        $issues = json_decode(file_get_contents($url), true);
        $issues = array_map(array($this, 'prepareIssue'), $issues['issues']);

        if (!empty($options['type'])) {
            $issues = array_filter($issues, function($issue) use ($options) {
                return in_array($issue['type'], $options['type']);
            });
        }
        if (!empty($options['assignee'])) {
            $issues = array_filter($issues, function($issue) use ($options) {
                return in_array($issue['assignee'], $options['assignee']);
            });
        }
        if (!empty($options['status'])) {
            $issues = array_filter($issues, function($issue) use ($options) {
                return in_array($issue['status'], $options['status']);
            });
        }

        if (!empty($options['sort'])) {
            if ($options['sort'] == 'priority') {
                usort($issues, array($this, 'sortByPriority'));
            }
        }

        return $issues;
    }

    /**
     * Query comments for a given issuse
     *
     * @param array issue details
     * @return array comments
     */
    public function findComments(array $issue)
    {
        $url = sprintf(
            'https://%s:%s@api.bitbucket.org/1.0/repositories/%s/issues/%d/comments',
            $this->config['username'],
            $this->config['password'],
            $this->config['repository'],
            $issue['id']
        );

        $comments = json_decode(file_get_contents($url), true);


        return array_reverse(array_map(array($this, 'prepareComment'), $comments));;
    }

    /**
     * Comment on an issue
     *
     * @param array issue details
     * @param string comment
     */
    public function comment(array $issue, $message)
    {
        $post = array('content' => $message);

        $url = sprintf(
            'https://api.bitbucket.org/1.0/repositories/%s/issues/%d/comments',
            $this->config['repository'],
            $issue['id']
        );

        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $url);
        curl_setopt($ch, \CURLOPT_POST, true);
        curl_setopt($ch, \CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, \CURLOPT_USERPWD, sprintf('%s:%s', $this->config['username'], $this->config['password']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($error = curl_error($ch)) {
            throw new \Exception($error);
        }
    }

    /**
     * Normalize incoming issues for application use
     *
     * @param array issue from bitbucket
     * @return array issue ready for use
     */
    protected function prepareIssue($issue)
    {
        return array(
            'id'            => $issue['local_id'],
            'title'         => $issue['title'],
            'description'   => $issue['content'],
            'type'          => $issue['metadata']['kind'],
            'assignee'      => isset($issue['responsible']) ? $issue['responsible']['username'] : '',
            'created'       => $this->parseDate($issue['created_on'], 'Europe/Amsterdam'),
            'updated'       => $this->parseDate($issue['utc_last_updated']),
            'comments'      => $issue['comment_count'],
            'priority'      => $this->priorities[$issue['priority']],
            'priority_text' => $issue['priority'],
            'status'        => $issue['status'],
            'url_endpoint'  => sprintf(
                'https://api.bitbucket.org/1.0/repositories/%s/issues/%d',
                $this->config['repository'],
                $issue['local_id']
            )
        );
    }

    /**
     * Normalize incoming comment for application use
     *
     * @param array comment from bitbucket
     * @return array comment ready for use
     */
    protected function prepareComment($comment)
    {
        return array(
            'username' => $comment['author_info']['username'],
            'date'     => $this->parseDate($comment['utc_created_on']),
            'message'  => $comment['content']
        );
    }

    /**
     * Sort priority
     *
     * @param issue a
     * @param issue b
     * @return -1 0 or 1
     */
    protected function sortByPriority($a, $b)
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }

        return $a['priority'] > $b['priority'] ? -1 : 1;
    }

    protected function parseDate($utcDate, $timezone = 'Etc/UTC')
    {
        $date = new \DateTime($utcDate, new \DateTimeZone($timezone));
        $date->setTimeZone(new \DateTimeZone('America/Vancouver'));

        return $date;
    }

    public function getBrowseUrl()
    {
        return sprintf(
            'https://bitbucket.org/%s/issues',
            $this->config['repository']
        );
    }

    public function getIssueUrl(array $issue)
    {
        return sprintf(
            'https://bitbucket.org/%s/issue/%d',
            $this->config['repository'],
            $issue['id']
        );
    }

    protected function request($method, $url)
    {
        if (strpos($url, 'http') === false) {
            $url = sprintf('https://api.bitbucket.org/1.0/repositories/%s%s', $this->config['repository'], $url);
        }

        $request = call_user_func(array($this->client, $method), $url);
        $request->setAuth($this->config['username'], $this->config['password']);

        return $request;
    }
}
