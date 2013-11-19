<?php

namespace Qissues\Trackers\Trello;

use Qissues\Model\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Tracker\IssueRepository;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\User;
use Guzzle\Http\Client;

class TrelloRepository implements IssueRepository
{
    protected $board;
    protected $query;
    protected $metadata;
    protected $mapping;
    protected $client;

    /**
     * @param string $board
     * @param string $key
     * @param string $token
     * @param TrelloMetadataBuilder $metadata
     * @param FieldMapping $mapping
     * @param Client|null $client to override
     */
    public function __construct($boardName, $key, $token, TrelloMetadataBuilder $metadata, FieldMapping $mapping, Client $client = null)
    {
        $this->board = $metadata->build();
        $this->query = array('key' => $key, 'token' => $token);
        $this->metadata = $metadata;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client('https://trello.com/', array('ssl.certificate_authority' => 'system'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://trello.com/b/%s', $this->board->getBoardId());
    }

    /**
     * {@inheritDoc}
     */
    public function lookup(Number $issue)
    {
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->board->getBoardId(), $issue));
        $request->getQuery()->set('actions', 'commentCard');
        $request->getQuery()->set('checklists', 'all');
        return $this->mapping->toIssue($request->send()->json());
    }

    /**
     * {@inheritDoc}
     */
    public function lookupUrl(Number $issue)
    {
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->board->getBoardId(), $issue));
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
        $request = $this->request('GET', sprintf('/boards/%s/cards/%s', $this->board->getBoardId(), $issue));
        $request->getQuery()->set('actions', 'commentCard');
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toComment'), $response['actions']);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(NewIssue $issue)
    {
        $request = $this->request('POST', sprintf('/repos/%s/issues', $this->repository));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $response = $request->send()->json();
        return new Number($response['number']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->repository, $number->getNumber()));
        $request->setBody(json_encode($this->mapping->issueToArray($issue)), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, NewComment $comment)
    {
        $request = $this->request('POST', $this->getIssueUrl($issue, '/comments'));
        $request->setBody(json_encode(array('body' => $comment->getMessage())), 'application/json');
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
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->repository, $issue->getNumber()));
        $request->setBody(json_encode(array('state' => $status->getStatus())), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        $request = $this->request('PATCH', sprintf('/repos/%s/issues/%d', $this->repository, $issue->getNumber()));
        $request->setBody(json_encode(array('assignee' => $user->getAccount())), 'application/json');
        $request->send();
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
}
