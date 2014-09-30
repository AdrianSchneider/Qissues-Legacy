<?php

namespace Qissues\Interfaces\Console\Input;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Milestone;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Model\SearchCriteria;

class ReportCriteriaBuilder
{
    public function build(array $reportConfig)
    {
        $criteria = new SearchCriteria;

        $this->handleKeywords($reportConfig, $criteria);
        $this->handleStatuses($reportConfig, $criteria);
        $this->handlePriorities($reportConfig, $criteria);
        $this->handleTypes($reportConfig, $criteria);
        $this->handleAssignees($reportConfig, $criteria);
        $this->handleMilestones($reportConfig, $criteria);
        $this->handleIds($reportConfig, $criteria);
        $this->handleSorting($reportConfig, $criteria);

        return $criteria;
    }

    protected function handleKeywords($reportConfig, $criteria)
    {
        if (!empty($reportConfig['keyword'])) {
            $criteria->setKeywords($reportConfig['keyword']);
        }
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

    protected function handleMilestones($reportConfig, $criteria)
    {
        if (!empty($reportConfig['milestones'])) {
            foreach ($reportConfig['milestones'] as $milestone) {
                $criteria->addMilestone(new Milestone($milestone));
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

    protected function handleSorting($reportConfig, $criteria)
    {
        if (!empty($reportConfig['sortFields'])) {
            foreach ($reportConfig['sortFields'] as $field) {
                $criteria->addSortField($field);
            }
        }
    }
}
