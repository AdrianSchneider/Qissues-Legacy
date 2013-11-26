<?php

namespace Qissues\Tests\Input;

use Qissues\Model\Querying\Number;
use Qissues\Console\Command\QueryCommand;
use Qissues\Console\Input\CriteriaBuilder;
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

    public function testByKeywords()
    {
        $input = new ArrayInput(array( '-k' => 'cheese'), $this->definition);

        $criteria = $this->builder->build($input);
        $this->assertEquals('cheese', $criteria->getKeywords());
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
        $this->assertInstanceOf('Qissues\Model\Meta\CurrentUser', $assignees[0]);
    }

    public function testHandleStatuses()
    {
        $input = new ArrayInput(array('-s' => array('open', 'closed')), $this->definition);

        $criteria = $this->builder->build($input);
        $statuses = $criteria->getStatuses();

        $this->assertCount(2, $statuses);
        $this->assertEquals('open', $statuses[0]->getStatus());
    }

    public function testHandleTextPriorities()
    {
        $input = new ArrayInput(array('-p' => array('urgent')), $this->definition);

        $criteria = $this->builder->build($input);
        $priorities = $criteria->getPriorities();

        $this->assertInstanceOf('Qissues\Model\Meta\Priority', $priorities[0]);
        $this->assertEquals(0, $priorities[0]->getPriority());
        $this->assertEquals('urgent', $priorities[0]->getName());
    }

    public function handleIntegerPriorities()
    {
        $input = new ArrayInput(array('-p' => array(5)), $this->definition);

        $criteria = $this->builder->build($input);
        $priorities = $criteria->getPriorities();

        $this->assertInstanceOf('Qissues\Model\Meta\Priority', $priorities[0]);
        $this->assertEquals(5, $priorities[0]->getPriority());
        $this->assertEquals('', $priorities[0]->getName());
    }

    public function testHandleAssignees()
    {
        $input = new ArrayInput(array('-a' => array('adrian')), $this->definition);

        $criteria = $this->builder->build($input);
        $assignees = $criteria->getAssignees();

        $this->assertCount(1, $assignees);
        $this->assertInstanceOf('Qissues\Model\Meta\User', $assignees[0]);
        $this->assertEquals('adrian', (string)$assignees[0]);
    }

    public function testHandleTypes()
    {
        $input = new ArrayInput(array('-t' => array('bug')), $this->definition);

        $criteria = $this->builder->build($input);
        $types = $criteria->getTypes();

        $this->assertCount(1, $types);
        $this->assertInstanceOf('Qissues\Model\Meta\Type', $types[0]);
        $this->assertEquals('bug', (string)$types[0]);
    }

    public function testHandleLabels()
    {
        $input = new ArrayInput(array('-l' => array('lame')), $this->definition);

        $criteria = $this->builder->build($input);
        $labels = $criteria->getLabels();

        $this->assertCount(1, $labels);
        $this->assertInstanceOf('Qissues\Model\Meta\Label', $labels[0]);
        $this->assertEquals('lame', (string)$labels[0]);
    }

    public function testHandleSorting()
    {
        $input = new ArrayInput(array('-o' => array('priority')), $this->definition);

        $criteria = $this->builder->build($input);
        $fields = $criteria->getSortFields();

        $this->assertEquals(array('priority'), $fields);
    }

    public function testHandlePaging()
    {
        $input = new ArrayInput(array('--limit' => 100, '--page' => 1), $this->definition);
        $criteria = $this->builder->build($input);

        $this->assertEquals(
            array(1, 100),
            $criteria->getPaging()
        );
    }
}
