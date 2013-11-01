<?php

namespace Qissues\Model\Querying;

use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Label;

/**
 * Value object for standardizing search criteria for trackers
 */
class SearchCriteria
{
    protected $numbers;
    protected $assigned;
    protected $statuses;
    protected $types;
    protected $priorities;
    protected $labels;
    protected $sortFields;
    protected $paging;

    public function __construct()
    {
        $this->numbers = array();
        $this->assigned = array();
        $this->statuses = array();
        $this->types = array();
        $this->priorities = array();
        $this->labels = array();
        $this->sortFields = array();
        $this->paging = array(0, 50);
    }

    public function addStatus(Status $status)
    {
        $this->statuses[] = $status;
    }

    public function getStatuses()
    {
        return $this->statuses;
    }

    public function addPriority(Priority $priority)
    {
        $this->priorities[] = $priority;
    }

    public function getPriorities()
    {
        return $this->priorities;
    }

    public function addType(Type $type)
    {
        $this->types[] = $type;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function setNumbers(array $numbers)
    {
        $this->numbers = $numbers;
    }

    public function getNumbers()
    {
        return $this->numbers;
    }

    public function addAssignee(User $user)
    {
        $this->assigned[] = $user;
    }

    public function getAssignees()
    {
        return $this->assigned;
    }

    public function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function addSortField($field)
    {
        $this->sortFields[] = $field;
    }

    public function getSortFields()
    {
        return $this->sortFields;
    }

    public function setPaging($offset, $limit)
    {
        $this->paging = array($offset, $limit);
    }

    public function getPaging()
    {
        return $this->paging;
    }
}
