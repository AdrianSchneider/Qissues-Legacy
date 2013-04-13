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

    protected $user = '';
    protected $pass = '';
    protected $repo = '';
    protected $ownr = '';

    public function find($id)
    {
        $url = sprintf(
            'https://%s:%s@api.bitbucket.org/1.0/repositories/%s/%s/issues/%d',
            $this->user,
            $this->pass,
            $this->repo,
            $this->ownr,
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
            'https://%s:%s@api.bitbucket.org/1.0/repositories/%s/%s/issues',
            $this->user,
            $this->pass,
            $this->repo,
            $this->ownr
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

    protected function prepareIssue($issue)
    {
        $issue['priority'] = $this->priorities[$issue['priority']];
        return $issue;
    }

    protected function sortByPriority($a, $b)
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }

        return $a['priority'] > $b['priority'] ? -1 : 1;
    }
}
