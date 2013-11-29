<?php

namespace Qissues\Tests\Model\Serializer;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Meta\Status;
use Qissues\Domain\Meta\Type;
use Qissues\Domain\Meta\User;
use Qissues\Domain\Meta\Label;
use Qissues\Domain\Meta\Priority;
use Qissues\Domain\Serializer\IssueSerializer;

class IssueSerializerTest extends \PHPUnit_Framework_TestCase
{
    protected function createBasicIssue()
    {
        return new Issue(
            5,
            'title',
            'description',
            new Status('broken'),
            new \DateTime('2010-01-01 00:00'),
            new \DateTime('2010-01-01 00:00')
        );
    }

    public function testFullIssueWorks()
    {
        $issue = new Issue(
            5,
            'title',
            'description',
            new Status('broken'),
            new \DateTime('2010-01-01 00:00'),
            new \DateTime('2010-01-01 00:00'),
            new User('adrian'),
            new Priority(1, 'urgent'),
            new Type('meh'),
            array(new Label('busted')),
            0
        );

        $serializer = new IssueSerializer();
        $serialized = $serializer->serialize($issue);

        $this->assertEquals('adrian', $serialized['assignee']);
        $this->assertEquals('urgent', $serialized['priority']);
        $this->assertEquals('meh', $serialized['type']);
        $this->assertEquals(array('busted'), $serialized['labels']);
    }

    public function testSerializesBasicFields()
    {
        $serializer = new IssueSerializer();
        $serialized = $serializer->serialize($this->createbasicIssue());

        $this->assertEquals(5, $serialized['number']);
        $this->assertEquals('title', $serialized['title']);
        $this->assertEquals('description', $serialized['description']);
        $this->assertEquals('broken', $serialized['status']);
        $this->assertEquals('2010-01-01 12:00am', $serialized['dateCreated']);
        $this->assertEquals('2010-01-01 12:00am', $serialized['dateUpdated']);
    }

    public function testEmptyTypeConvertsToEmptyString()
    {
        $serializer = new IssueSerializer();
        $serialized = $serializer->serialize($this->createbasicIssue());
        $this->assertEquals('', $serialized['type']);
    }

    public function testEmptyPriorityConvertsToZero()
    {
        $serializer = new IssueSerializer();
        $serialized = $serializer->serialize($this->createbasicIssue());
        $this->assertEquals(0, $serialized['priority']);
    }

    public function testNoAssigneeConvertsToEmptyString()
    {
        $serializer = new IssueSerializer();
        $serialized = $serializer->serialize($this->createbasicIssue());
        $this->assertEquals('', $serialized['assignee']);
    }

    public function testNoLabelsConvertsToEmptyArray()
    {
        $serializer = new IssueSerializer();
        $serialized = $serializer->serialize($this->createbasicIssue());
        $this->assertEquals(array(), $serialized['labels']);
    }
}
