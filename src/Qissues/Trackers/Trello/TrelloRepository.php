<?php

namespace Qissues\Trackers\Trello;

use Qissues\Model\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Tracker\IssueRepository;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Tracker\Metadata\Metadata;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\ClosedStatus;
use Qissues\Model\Meta\User;
use Guzzle\Http\Client;

class TrelloRepository implements IssueRepository
{
    protected $boardName;
    protected $query;
    protected $mapping;
    protected $storage;
    protected $metadata;
    protected $client;

    /**
     * @param string $board
     * @param string $key
     * @param string $token
     * @param FieldMapping $mapping
     * @param Metadata $metadata
     * @param Client|null $client to override
     */
    public function __construct($boardName, $key, $token, FieldMapping $mapping, Metadata $metadata, Client $client = null)
    {
        $this->boardName = $boardName;
        $this->query = array('key' => $key, 'token' => $token);
        $this->mapping = $mapping;
        $this->metadata = $metadata;
        $this->client  = $client ?: new Client('https://trello.com/', array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://trello.com/b/%s', $this->metadata->getBoardId());
    }

    /**
     * {@inheritDoc}
     */
    public function lookup(Number $issue)
    {
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->metadata->getBoardId(), $issue));
        $request->getQuery()->set('actions', 'commentCard');
        $request->getQuery()->set('checklists', 'all');
        return $this->mapping->toIssue($request->send()->json());
    }

    /**
     * Lookup an ID using the short number
     * @param Number $number
     * @return string qualified ID
     */
    protected function lookupId(Number $issue)
    {
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->metadata->getBoardId(), $issue));
        $response = $request->send()->json();
        return $response['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function lookupUrl(Number $issue)
    {
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->metadata->getBoardId(), $issue));
        $rawIssue = $request->send()->json();
        return $rawIssue['url'];
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $query = $this->mapping->buildSearchQuery($criteria);

        $request = $this->request('GET', $query['endpoint']);
        $request->getQuery()->merge($query['params']);
        $response = $request->send()->json();

        $issues = array_map(array($this->mapping, 'toIssue'), $response);
        return $this->mapping->filterIssues($issues, $criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findComments(Number $issue)
    {
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->metadata->getBoardId(), $issue));
        $request->getQuery()->set('actions', 'commentCard');
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toComment'), $response['actions']);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(NewIssue $issue)
    {
        $request = $this->request('POST', "/cards");
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $response = $request->send()->json();
        return new Number($response['idShort']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        $request = $this->request('PUT', sprintf("/cards/%s", $this->lookupId($number)));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $response = $request->send()->json();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, NewComment $comment)
    {
        $request = $this->request('POST', sprintf("/cards/%s/actions/comments", $this->lookupId($issue)));
        $request->setBody(json_encode(array('text' => $comment->getMessage())), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Number $issue)
    {
        $request = $this->request('DELETE', sprintf("/cards/%s", $this->lookupId($issue)));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function changeStatus(Number $issue, Status $status)
    {
        if ($status instanceof ClosedStatus) {
            $request = $this->request('PUT', sprintf("/cards/%s", $this->lookupId($issue)));
            $request->setBody(json_encode(array('closed' => true)), 'application/json');
            $request->send();
            return;
        }

        $request = $this->request('PUT', sprintf("/cards/%s", $this->lookupId($issue)));
        $request->setBody(json_encode(array('idList' => $this->metadata->getListIdByName($status->getStatus()))), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        throw new \Exception('wip');
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
        $request = call_user_func(array($this->client, $method), "/1" . $url);
        $request->getQuery()->merge($this->query);
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
        return sprintf('/repos/%s/issues/%d%s', $this->repository, $number->getNumber(), $append);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata()
    {
        $request = $this->client->get('/1/members/my/boards');
        $request->getQuery()->merge($this->query);
        $request->getQuery()->merge(array('lists' => 'open'));

        $response = $request->send()->json();
        $found = false;

        foreach ($response as $board) {
            if ($board['name'] != $this->boardName) {
                continue;
            }

            $found = true;
            $lists = array();
            foreach ($board['lists'] as $list) {
                $lists[$list['name']] = array(
                    'id' => $list['id'],
                    'name' => $list['name'],
                    'pos' => $list['pos']
                );
            }

            usort($lists, function($a, $b) {
                if ($a['pos'] == $b['pos']) return 0;
                return $a['pos'] < $b['pos'] ? -1 : 1;
            });


            return array(
                'id' => $board['id'],
                'name' => $board['name'],
                'labels' => $board['labelNames'],
                'lists' => $lists
            );
        }

        if (!$found) {
            throw new \Exception('Could not find Trello board; do you have the right name?');
        }

    }

    public function buildMetadata(array $metadata)
    {
        return new Metadata($metadata);
    }

    public function refreshMetadata()
    {
        $this->metadata->update();
    }
}