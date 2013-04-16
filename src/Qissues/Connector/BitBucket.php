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
        $this->changeFields($issue, array(
            'title'       => $changes['title'],
            'priority'    => $changes['priority'],
            'content'     => $changes['description'],
            'kind'        => $changes['kind'],
            'responsible' => $changes['assignee']
        ));
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
        $url = sprintf(
            'https://api.bitbucket.org/1.0/repositories/%s/issues/' . $issue['local_id'],
            $this->config['repository']
        );

        $ch = curl_init();
        curl_setopt($ch, \CURLOPT_URL, $url);
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
            'https://%s:%s@api.bitbucket.org/1.0/repositories/%s/issues?limit=%d',
            urlencode($this->config['username']),
            urlencode($this->config['password']),
            $this->config['repository'],
            $options['limit']
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
        if (!empty($options['status'])) {
            $issues = array_filter($issues, function($issue) use ($options) {
                if (strpos($options['status'], ',') !== false) {
                    return in_array($issue['status'], explode(',', $options['status']));
                }
                return $issue['status'] == $options['status'];
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


        return array_map(array($this, 'prepareComment'), $comments);
    }

    /**
     * Comment on an issue
     *
     * @param array issue details
     * @param string comment
     */
    public function comment(array $issue, $message)
    {
        $ch = curl_init();

        $post = array('content' => $message);

        $url = sprintf(
            'https://api.bitbucket.org/1.0/repositories/%s/issues/%d/comments',
            $this->config['repository'],
            $issue['local_id']
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
            'status'        => $issue['status']
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
        $comment['username'] = $comment['author_info']['username'];
        $comment['date'] = $this->parseDate($comment['utc_created_on']);

        return $comment;
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
}
