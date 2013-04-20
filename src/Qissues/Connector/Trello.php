<?php

namespace Qissues\Connector;

use Guzzle\Http\Client;

class Trello implements Connector
{
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->config['statusMap'] = array_flip($this->config['statuses']);
        $this->config['membersMap'] = array_flip($this->config['members']);
        $this->client = new Client('https://api.trello.com/', array(
            'ssl.certificate_authority' => 'system'
        ));
    }

    public function create(array $issue)
    {

    }

    public function update(array $changes, array $issue)
    {

    }

    public function delete(array $issue)
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
        $request = $this->request('get', sprintf('/boards/%s/cards/%s', $this->config['board'], $id));
        $request->getQuery()->set('actions_entities', true);
        return $this->prepareIssue($request->send()->json());
    }

    public function findAll(array $options)
    {
        $request = $this->request('get', sprintf('/boards/%s/cards', $this->config['board']));
        $issues = $request->send()->json();

        $trello = $this;
        $issues = array_map(array($this, 'prepareIssue'), $issues);
        $issues = array_filter($issues, function(array $issue) use ($options, $trello) {
            return $trello->filterIssue($issue, $options);
        });

        if (in_array('priority', $options['sort'])) {
            usort($issues, function($a, $b) {
                if ($a['priority'] == $b['priority']) {
                    return 0;
                }
                return $a['priority'] < $b['priority'] ? -1 : 1;
            });
        }

        return $issues;
    }

    /**
     * Converts a card from trello into a compatible issue
     *
     * @param array card details
     * @return array issue
     */
    protected function prepareIssue(array $card)
    {
        $memberMap = $this->config['membersMap'];
        return array(
            'id'            => $card['idShort'],
            'id_long'       => $card['id'],
            'title'         => $card['name'],
            'description'   => $card['desc'],
            'assignee'      => implode(',', array_map(function($member) use ($memberMap) {
                return $memberMap[$member];
            }, $card['idMembers'])),
            'created'       => new \DateTime($card['dateLastActivity']),
            'updated'       => new \DateTime($card['dateLastActivity']),
            'status'        => $this->config['statusMap'][$card['idList']],
            'priority'      => $card['pos'],
            'priority_text' => 'n/a',
            'type'          => 'bug',
            'comments'      => 0
        );
    }

    public function filterIssue(array $issue, array $options)
    {
        if (!empty($options['priority'])) {
            throw new \LogicException('Trello does not support priorities');
        }

        if (!empty($options['assignee'])) {
            $names = explode(',', $issue['assignee']);
            if (!array_intersect($names, $options['assignee'])) {
                return false;
            }
        }


        if ($options['status'] !== array('open')) {
            if (!in_array($issue['status'], $options['status'])) {
                return false;
            }
        }

        if (!empty($options['type'])) {
            if (!in_array($issue['type'], $options['type'])) {
                return false;
            }
        }

        return true;
    }

    public function findComments(array $issue)
    {
        return array();

        $request = $this->request('get', sprintf('/boards/%s/cards/%s/actions', $this->config['board'], $issue['id_long']));
        $request->getQuery()->set('actions_entities', true);
        return $this->prepareIssue($request->send()->json());
    }

    public function comment(array $issue, $message)
    {
        $request = $this->request('post', sprintf('/cards/%s/actions/comments', $issue['id_long']));
        $request->getQuery()->set('text', $message);
        $request->send();
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
            $url = 'https://api.trello.com/1' . $url;
        }

        $request = call_user_func(array($this->client, $method), $url);
        $request->getQuery()->merge(array(
            'key' => $this->config['key'],
            'token' => $this->config['token']
        ));

        return $request;
    }
}
