<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Tracker\IssueRepository;
use Qissues\Model\Tracker\FieldMapping;
use Qissues\Model\Tracker\Metadata\NullMetadata;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\ClosedStatus;
use Qissues\Model\Meta\User;
use Guzzle\Http\Client;
use Guzzle\Http\QueryAggregator\DuplicateAggregator;

class JiraRepository implements IssueRepository
{
    protected $repository;
    protected $username;
    protected $password;
    protected $mapping;
    protected $client;

    /**
     * @param string $project ex "project"(.atlassian.net)
     * @param string $prefix ex "PROJ"
     * @param string username
     * @param string password
     * @param IssueTracker $tracker
     * @param Client|null $client to override
     */
    public function __construct($project, $prefix, $username, $password, FieldMapping $mapping, Client $client = null)
    {
        $this->project = $project;
        $this->prefix = $prefix;
        $this->username = $username;
        $this->password = $password;
        $this->mapping = $mapping;
        $this->client  = $client ?: new Client(
            sprintf('https://%s.atlassian.net/', $this->project),
            array('ssl.certificate_authority' => 'system')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return sprintf('https://%s.atlassian.net/issues', $this->project);
    }

    /**
     * {@inheritDoc}
     */
    public function lookup(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue));
        $data = $request->send()->json();
        return $this->mapping->toIssue($data);
    }

    /**
     * {@inheritDoc}
     */
    public function lookupUrl(Number $issue)
    {
        return sprintf(
            'https://%s.atlassian.net/browse/%s-%d',
            $this->project,
            $this->prefix,
            $issue->getNumber()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function query(SearchCriteria $criteria)
    {
        $mapping = $this->mapping->buildSearchQuery($criteria);
        $query = array_merge($mapping['paging'], array('jql' => $this->buildJql($mapping)));

        $request = $this->request('GET', "/search");
        $request->getQuery()->merge($query);
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toIssue'), $response['issues']);
    }

    /**
     * Constructs the JQL query from the translated field names
     * @param array $query (usually http) field names
     * @return string JQL
     */
    protected function buildJql(array $query)
    {
        unset($query['paging']);

        if (!empty($query['sort'])) {
            $sort = $query['sort'];
            unset($query['sort']);
        } else {
            $sort = array();
        }

        $quote = function($string) { return "'" . addslashes($string) . "'"; };

        $where = array();
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                $where[] = sprintf(
                    '%s IN (%s)',
                    $key,
                    implode(',', array_map($quote, $value))
                );
            } else {
                $where[] = sprintf('%s = %s', $key, $quote($value));
            }
        }

        return sprintf(
            '%s %s',
            implode(' AND ', $where),
            $sort ? sprintf(
                'ORDER BY %s',
                implode(', ', $sort)
            ) : ''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findComments(Number $issue)
    {
        $request = $this->request('GET', $this->getIssueUrl($issue, '/comment'));
        $response = $request->send()->json();
        return array_map(array($this->mapping, 'toComment'), $response['comments']);
    }

    /**
     * {@inheritDoc}
     */
    public function persist(NewIssue $issue)
    {
        throw new \Exception('not yet implemented');
        $request = $this->request('POST', sprintf('/repositories/%s/issues', $this->repository));
        $request->setBody($this->mapping->issueToArray($issue));
        $response = $request->send()->json();
        return new Number($response['local_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function update(NewIssue $issue, Number $number)
    {
        throw new \Exception('not yet implemented');
        $request = $this->request('PUT', $this->getIssueUrl($number));
        $request->setBody($this->mapping->issueToArray($issue));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function comment(Number $issue, NewComment $comment)
    {
        $request = $this->request('POST', $this->getIssueUrl($issue, '/comment'));
        $request->setBody(json_encode(array('body' => $comment->getMessage())), 'application/json');
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Number $issue)
    {
        $request = $this->request('DELETE', $this->getIssueUrl($issue, '/comments'));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function changeStatus(Number $issue, Status $status)
    {
        if ($status instanceof ClosedStatus) {
            $status = new Status('resolved');
        }

        $request = $this->request('PUT', $this->getIssueUrl($issue));
        $request->setBody(array('status' => $status->getStatus()));
        $request->send();
    }

    /**
     * {@inheritDoc}
     */
    public function assign(Number $issue, User $user)
    {
        $request = $this->request('PUT', $this->getIssueUrl($issue));
        $request->setBody(array('responsible' => $user->getAccount()));
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
        $url = 'rest/api/2' . $url;
        $request = call_user_func(array($this->client, $method), $url);
        $request->setAuth($this->username, $this->password);
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
        return sprintf(
            '/issue/%s-%d%s', 
            $this->prefix,
            $number->getNumber(),
            $append
        );
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata()
    {
        throw new \Exception('No metadata necessary for BitBucket');
    }

    /**
     * {@inheritDoc}
     */
    public function buildMetadata(array $metadata)
    {
        return new NullMetadata;
    }
}
