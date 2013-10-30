<?php

namespace Qissues\Input;

use Qissues\Model\SearchCriteria;
use Qissues\Model\Number;
use Qissues\Model\User;
use Qissues\Model\CurrentUser;
use Qissues\Model\Status;
use Qissues\Model\Priority;
use Qissues\Model\Type;
use Symfony\Component\Console\Input\InputInterface;

class CriteriaBuilder
{
    public function build(InputInterface $input)
    {
        $criteria = new SearchCriteria();

        $this->handleStatuses($input, $criteria);
        $this->handlePriorities($input, $criteria);
        $this->handleTypes($input, $criteria);
        $this->handleAssignees($input, $criteria);
        $this->handleIds($input, $criteria);

        return $criteria;
    }

    protected function handleStatuses($input, $criteria)
    {
        if ($statuses = $input->getOption('status')) {
            foreach ($statuses as $status) {
                $criteria->addStatus(new Status($status));
            }
        }
    }

    protected function handlePriorities($input, $criteria)
    {
        if ($priorities = $input->getOption('priority')) {
            foreach ($priorities as $priority) {
                $criteria->addPriority(new Priority($priority));
            }
        }
    }

    protected function handleTypes($input, $criteria)
    {
        if ($types = $input->getOption('type')) {
            foreach ($types as $type) {
                $criteria->addType(new Type($type));
            }
        }
    }

    protected function handleAssignees($input, $criteria)
    {
        if ($assignees = $input->getOption('assignee')) {
            foreach ($assignees as $assignee) {
                $criteria->addAssignee(new User($assignee));
            }
        }
        if ($input->getOption('mine')) {
            $criteria->addAssignee(new CurrentUser());
        }
    }

    protected function handleIds($input, $criteria)
    {
        if ($ids = $input->getOption('ids')) {
            $numbers = array();
            if (strpos($ids[0], ',') !== false) {
                $ids = explode(',', $ids[0]);
            }

            foreach ($ids as $id) {
                $numbers[] = new Number($id);
            }

            $criteria->setNumbers($numbers);
        }
    }
}
