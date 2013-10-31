<?php

namespace Qissues\Console\Input;

use Qissues\Model\Number;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\CurrentUser;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Type;
use Qissues\Model\Querying\SearchCriteria;

class ReportCriteriaBuilder
{
    public function build(array $reportConfig)
    {
        $criteria = new SearchCriteria;

        $this->handleStatuses($reportConfig, $criteria);
        $this->handlePriorities($reportConfig, $criteria);
        $this->handleTypes($reportConfig, $criteria);
        $this->handleAssignees($reportConfig, $criteria);
        $this->handleIds($reportConfig, $criteria);

        return $criteria;
    }

    protected function handleStatuses($reportConfig, $criteria)
    {
        if (!empty($reportConfig['statuses'])) {
            foreach ($reportConfig['statuses'] as $status) {
                $criteria->addStatus(new Status($status));
            }
        }
    }

    protected function handlePriorities($reportConfig, $criteria)
    {
        if (!empty($reportConfig['priorities'])) {
            foreach ($reportConfig['priorities'] as $priority) {
                $criteria->addPriority(new Priority($priority, ''));
            }
        }
    }

    protected function handleTypes($reportConfig, $criteria)
    {
        if (!empty($reportConfig['types'])) {
            foreach ($reportConfig['types'] as $type) {
                $criteria->addType(new Type($type));
            }
        }
    }

    protected function handleAssignees($reportConfig, $criteria)
    {
        if (!empty($reportConfig['assignees'])) {
            foreach ($reportConfig['assignees'] as $assignee) {
                $criteria->addAssignee(new User($assignee));
            }
        }
    }

    protected function handleIds($reportConfig, $criteria)
    {
        if (!empty($reportConfig['ids'])) {
            $numbers = array();
            foreach ($reportConfig['ids'] as $id) {
                $numbers[] = new Number($id);
            }

            $criteria->setNumbers($numbers);
        }
    }
}
