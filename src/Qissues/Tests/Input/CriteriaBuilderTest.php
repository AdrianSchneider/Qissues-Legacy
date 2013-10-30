<?php

namespace Qissues\Tests\Input;

use Qissues\Model\Number;
use Qissues\Command\QueryCommand;
use Qissues\Input\CriteriaBuilder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command;

class CriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;
    protected $setup = false;

    public function setUp()
    {
        if ($this->setup) {
            return;
        }

        $this->builder = new CriteriaBuilder();
        $command = new QueryCommand('query-test');
        $this->definition = $command->getDefinition();

        $this->setup = true;
    }

    public function testByNumber()
    {
        $input = new ArrayInput(array( '-i' => array(1, 2, 3)), $this->definition);

        $criteria = $this->builder->build($input);
        $this->assertEquals(array(
            new Number(1),
            new Number(2),
            new Number(3)
        ), $criteria->getNumbers());
    }

    public function testByNumbersDelimitedByCommas()
    {
        $input = new ArrayInput(array('-i' => array('1,2,3')), $this->definition);

        $criteria = $this->builder->build($input);
        $this->assertEquals(array(
            new Number(1),
            new Number(2),
            new Number(3)
        ), $criteria->getNumbers());
    }

    public function testHandleMine()
    {
        $input = new ArrayInput(array('--mine' => true), $this->definition);

        $criteria = $this->builder->build($input);
        $assignees = $criteria->getAssignees();

        $this->assertCount(1, $assignees);
        $this->assertInstanceOf('Qissues\Model\CurrentUser', $assignees[0]);
    }

    public function testHandleStatuses()
    {
        $input = new ArrayInput(array('-s' => array('open', 'closed')), $this->definition);

        $criteria = $this->builder->build($input);
        $statuses = $criteria->getStatuses();

        $this->assertCount(2, $statuses);
        $this->assertEquals('open', $statuses[0]->getStatus());
    }

    public function testHandlePriorities()
    {
        $input = new ArrayInput(array('-p' => array(5)), $this->definition);

        $criteria = $this->builder->build($input);
        $priorities = $criteria->getPriorities();

        $this->assertInstanceOf('Qissues\Model\Priority', $priorities[0]);
    }

    public function testHandleAssignees()
    {
        $input = new ArrayInput(array('-a' => array('adrian')), $this->definition);

        $criteria = $this->builder->build($input);
        $assignees = $criteria->getAssignees();

        $this->assertCount(1, $assignees);
        $this->assertInstanceOf('Qissues\Model\User', $assignees[0]);
        $this->assertEquals('adrian', (string)$assignees[0]);
    }

    public function testHandleTypes()
    {
        $input = new ArrayInput(array('-t' => array('bug')), $this->definition);

        $criteria = $this->builder->build($input);
        $types = $criteria->getTypes();

        $this->assertCount(1, $types);
        $this->assertInstanceOf('Qissues\Model\Type', $types[0]);
        $this->assertEquals('bug', (string)$types[0]);
    }
}
