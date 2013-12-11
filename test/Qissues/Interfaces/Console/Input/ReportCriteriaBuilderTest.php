<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Interfaces\Console\Input\ReportCriteriaBuilder;

class ReportCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildsASearchCriteria()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array());

        $this->assertInstanceOf('Qissues\Domain\Model\SearchCriteria', $criteria);
    }

    public function testHandlesKeywords()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('keyword' => 'pizza'));
        $this->assertEquals('pizza', $criteria->getKeywords());
    }

    public function testHandlesStatuses()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('statuses' => array( 'open', 'closed')));

        $statuses = $criteria->getStatuses();
        $this->assertEquals('open', $statuses[0]->getStatus());
        $this->assertEquals('closed', $statuses[1]->getStatus());
    }

    public function testHandlePriorities()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('priorities' => array(1, 2, 3)));

        $priorities = $criteria->getPriorities();
        $this->assertEquals(1, $priorities[0]->getPriority());
        $this->assertEquals(2, $priorities[1]->getPriority());
        $this->assertEquals(3, $priorities[2]->getPriority());
    }

    public function testHandleTypes()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('types' => array('bug')));

        $types = $criteria->getTypes();
        $this->assertEquals('bug', $types[0]->getName());
    }

    public function testHandleAssignees()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('assignees' => array('adrian')));

        $assignees = $criteria->getAssignees();
        $this->assertEquals('adrian', $assignees[0]->getAccount());
    }

    public function testHandleIds()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('ids' => array(5, 4, 3, 2, 1)));

        $ids = $criteria->getNumbers();
        $this->assertEquals(array(5, 4, 3, 2, 1), array_map('strval', $ids));
    }

    public function testHandleSorting()
    {
        $builder = new ReportCriteriaBuilder();
        $criteria = $builder->build(array('sortFields' => array('a', 'b')));

        $this->assertEquals(array('a', 'b'), $criteria->getSortFields());
    }
}
