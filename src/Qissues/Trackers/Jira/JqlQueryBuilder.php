<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Model\SearchCriteria;
use Qissues\Trackers\Shared\Metadata\Metadata;

class JqlQueryBuilder
{
    protected $metadata;
    protected $where;
    protected $sort;

    public function __construct(Metadata $metadata)
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

        $this->handleStatuses($criteria);
        $this->handleTypes($criteria);
        $this->handleAssignees($criteria);
        $this->handleKeywords($criteria);
        $this->handleSorting($criteria);
        $this->handleIds($criteria);

        return $this->generateJql($this->where, $this->sort);
    }

    /**
     * Filter by statuses if specified
     * @param SearchCriteria $criteria
     */
    protected function handleStatuses(SearchCriteria $criteria)
    {
        if ($statuses = $criteria->getStatuses()) {
            $this->where[] = $this->whereEquals('status', array_map('strval', $statuses));
        }
    }

    /**
     * Filter by types if specified
     * @param SearchCriteria $criteria
     */
    protected function handleTypes(SearchCriteria $criteria)
    {
        if ($types = $criteria->getTypes()) {
            $this->where[] = $this->whereEquals('issuetype', array_map('strval', $types));
        }
    }

    /**
     * Filter by assignees if specified
     * @param SearchCriteria $criteria
     */
    protected function handleAssignees(SearchCriteria $criteria)
    {
        if ($assignees = $criteria->getAssignees()) {
            $this->where[] = $this->whereEquals('assignee', array_map('strval', $assignees));
        }
    }

    /**
     * Filter by keywords if specified
     * @param SearchCriteria $criteria
     */
    protected function handleKeywords(SearchCriteria $criteria)
    {
        if ($keywords = $criteria->getKeywords()) {
            $this->where[] = sprintf('text ~ %s', $this->quote($keywords));
        }
    }

    /**
     * Filter by ids if specified
     * @param SearchCriteria $criteria
     */
    protected function handleIds(SearchCriteria $criteria)
    {
        if ($ids = $criteria->getNumbers()) {
            $key = $this->metadata->getKey();
            $this->where[] = $this->whereEquals(
                'id',
                array_map(function($id) use ($key) {
                    return $key . '-' . $id;
                }, $ids)
            );
        }
    }

    /**
     * Add sorting, if specified
     * @param SearchCriteria $criteria
     */
    protected function handleSorting(SearchCriteria $criteria)
    {
        $fieldMap = array(
            'updated' => 'updatedDate',
            'created' => 'createdDate'
        );

        $fieldSort = array(
            'priority' => 'DESC',
            'updatedDate' => 'DESC',
            'createdDate' => 'DESC'
        );

        if ($fields = $criteria->getSortFields()) {
            foreach ($fields as $field) {
                if (isset($fieldMap[$field])) {
                    $field = $fieldMap[$field];
                }

                if (isset($fieldSort[$field])) {
                    $this->sort[] = "$field " . $fieldSort[$field];
                } else {
                    throw new \DomainException("JIRA cannot sort by '$field'");
                }
            }
        }
    }

    /**
     * Constructs a where clause, assuming a = b, or a in (b, c)
     *
     * @param string $field name
     * @param string|array $values
     */
    protected function whereEquals($field, $values)
    {
        if (is_array($values)) {
            return sprintf('%s IN (%s)', $field, $this->quoteArray($values));
        }

        return sprintf('%s = %s', $field, $this->quote($values));
    }

    /**
     * Quote an array of literals
     *
     * @param array $literals
     * @return string comma-delimited escaped literals
     */
    protected function quoteArray(array $literals)
    {
        return implode(',', array_map(array($this, 'quote'), $literals));
    }

    /**
     * Quotes a string for usage
     * Injection isn't a concern here; just user annoyance
     *
     * @param mixed $literal
     * @return string quoted string with quotes escaped
     */
    protected function quote($literal)
    {
        return "'" . addslashes($literal) . "'";
    }

    /**
     * Generate the final JQL for returning
     * @param array $where conditions
     * @param array $sort clauses
     */
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
