<?php

namespace Qissues\Connector;

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
    }

    /**
     * Creates a new Issue
     *
     * @param array issue details
     * @return array created issue from BitBucket
     */
    public function create(array $issue)
    {
        $ch = curl_init();

        $post = array(
            'title'       => $issue['title'],
            'priority'    => $issue['priority'],
            'content'     => $issue['description'],
            'kind'        => $issue['kind'],
            'responsible' => $issue['assignee']
        );

        $url = sprintf(
            'https://api.bitbucket.org/1.0/repositories/%s/issues',
            $this->config['repository']
        );

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
        $ch = curl_init();

        $post = array(
            'title'       => $changes['title'],
            'priority'    => $changes['priority'],
            'content'     => $changes['description'],
            'kind'        => $changes['kind'],
            'responsible' => $changes['assignee']
        );

        $url = sprintf(
            'https://api.bitbucket.org/1.0/repositories/%s/issues/' . $issue['local_id'],
            $this->config['repository']
        );

        curl_setopt($ch, \CURLOPT_URL, $url);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, \CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, \CURLOPT_USERPWD, sprintf('%s:%s', $this->config['username'], $this->config['password']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if ($error = curl_error($ch)) {
            throw new \Exception($error);
        }
    }

    /**
     * Find an issue by its ID
     *
     * @param integer ID
     * @return array issue details
     */
    public function find($id)
    {
        $url = sprintf(
            'https://%s:%s@api.bitbucket.org/1.0/repositories/%s/issues/%d',
            $this->config['username'],
            $this->config['password'],
            $this->config['repository'],
            $id
        );

        $issue = json_decode(file_get_contents($url), true);
        if (!$issue) {
            return;
        }
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
        $url = sprintf(
            'https://%s:%s@api.bitbucket.org/1.0/repositories/%s/issues',
            $this->config['username'],
            $this->config['password'],
            $this->config['repository']
        );

        $issues = json_decode(file_get_contents($url), true);
        $issues = array_map(array($this, 'prepareIssue'), $issues['issues']);

        if (!empty($options['kind'])) {
            $issues = array_filter($issues, function($issue) use ($options) {
                return $issue['metadata']['kind'] == $options['kind'];
            });
        }
        if (!empty($options['assignee'])) {
            $issues = array_filter($issues, function($issue) use ($options) {
                return isset($issue['responsible']) && $issue['responsible']['username'] == $options['assignee'];
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
     * Normalize incoming issues for application use
     *
     * @param array issue from bitbucket
     * @return array issue ready for use
     */
    protected function prepareIssue($issue)
    {
        $issue['priority'] = $this->priorities[$issue['priority']];
        $issue['assignee'] = $issue['responsible']['username'];
        $issue['kind'] = $issue['metadata']['kind'];
        return $issue;
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
}
