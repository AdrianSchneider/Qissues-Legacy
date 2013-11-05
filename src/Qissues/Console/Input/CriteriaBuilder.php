<?php

namespace Qissues\Console\Input;

use Qissues\Model\Number;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\CurrentUser;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Label;
use Symfony\Component\Console\Input\InputInterface;

class CriteriaBuilder
{
    public function build(InputInterface $input)
    {
        $criteria = new SearchCriteria();

        $this->handleKeywords($input, $criteria);
        $this->handleStatuses($input, $criteria);
        $this->handlePriorities($input, $criteria);
        $this->handleTypes($input, $criteria);
        $this->handleAssignees($input, $criteria);
        $this->handleIds($input, $criteria);
        $this->handleLabels($input, $criteria);

        $this->handleSorting($input, $criteria);
        $this->handlePaging($input, $criteria);

        return $criteria;
    }

    protected function handleKeywords($input, $criteria)
    {
        if ($keywords = $input->getOption('keyword')) {
            $criteria->setKeywords($keywords);
        }
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
                if (is_numeric($priority)) {
                    $criteria->addPriority(new Priority($priority, ''));
                } else {
                    $criteria->addPriority(new Priority(0, $priority));
                }
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

    protected function handleLabels($input, $criteria)
    {
        if ($labels = $input->getOption('labels')) {
            foreach ($labels as $label) {
                $criteria->addLabel(new Label($label));
            }
        }
    }

    protected function handleSorting($input,  $criteria)
    {
        if ($sort = $input->getOption('sort')) {
            foreach ($sort as $field) {
                $criteria->addSortField($field);
            }
        }
    }

    protected function handlePaging($input, $criteria)
    {
        $criteria->setPaging(
            $input->getOption('page', 1),
            $input->getOption('limit', 50)
        );
    }
}
