<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Querying\SearchCriteria;

class JqlQueryBuilder
{
    protected $metadata;
    protected $where;
    protected $sort;

    public function __construct(JiraMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Constructs a JQL query from SearchCriteria
     * @see https://confluence.atlassian.com/display/JIRA/Advanced+Searching
     *   for JQL reference
     *
     * @param SearchCriteria $criteria
     * @return string JQL
     */
    public function build(SearchCriteria $criteria)
    {
        $this->where = array();
        $this->sort = array();

        $this->where[] = $this->whereEquals('project', $this->metadata->getId());

        $this->handleTypes($criteria);
        $this->handleAssignees($criteria);

        return $this->generateJql($this->where, $this->sort);
    }

    protected function handleTypes(SearchCriteria $criteria)
    {
        if ($types = $criteria->getTypes()) {
            $this->where[] = $this->whereEquals('issuetype', array_map('strval', $types));
        }
    }

    protected function handleAssignees(SearchCriteria $criteria)
    {
        if ($assignees = $criteria->getAssignees()) {
            $this->where[] = $this->whereEquals('assignee', array_map('strval', $assignees));
        }
    }

    protected function whereEquals($field, $values)
    {
        if (is_array($values)) {
            return sprintf('%s IN (%s)', $field, $this->quoteArray($values));
        }

        return sprintf('%s = %s', $field, $this->quote($values));
    }

    protected function quoteArray(array $literals)
    {
        return implode(',', array_map(array($this, 'quote'), $literals));
    }

    protected function quote($literal)
    {
        return "'" . addslashes($literal) . "'";
    }

    protected function generateJql(array $where, array $sort)
    {
        return trim(sprintf(
            '%s %s',
            implode(' AND ', $where),
            $sort ? sprintf(
                'ORDER BY %s',
                implode(', ', $sort)
            ) : ''
        ));
    }
}
