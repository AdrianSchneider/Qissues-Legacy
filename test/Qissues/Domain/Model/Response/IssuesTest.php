<?php

namespace Qissues\Domain\Model\Response;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Response\Issues;
use Qissues\Domain\Shared\Status;

class IssuesTest extends \PHPUnit_Framework_TestCase
{
    public function testOnlyAcceptsIssues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Issue');
        $issues = new Issues(array('not an issue'));
    }

    public function testIsCountable()
    {
        $issues = new Issues(array($this->getIssue(), $this->getIssue()));
        $this->assertCount(2, $issues);
    }

    public function testIsIterable()
    {
        $issues = new Issues(array($i = $this->getIssue()));
        $counter = 0;
        foreach ($issues as $issue) {
            $counter++;
            $this->assertSame($i, $issue);
        }
        $this->assertEquals(1, $counter);
    }

    public function testOffsetExists()
    {
        $issues = new Issues(array());
        $this->assertFalse(isset($issues[0]));
    }

    public function testOffsetGet()
    {
        $issues = new Issues(array($issue = $this->getIssue()));
        $this->assertSame($issue, $issues[0]);
    }

    public function testOffsetSetThrowsException()
    {
        $this->setExpectedException('BadMethodCallException', 'immutable');
        $issues = new Issues(array());
        $issues[0] = 'anything';
    }

    public function testOffsetUnsetThrowsException()
    {
        $this->setExpectedException('BadMethodCallException', 'immutable');
        $issues = new Issues(array());
        unset($issues[0]);
    }

    public function testFilterReturnsNewFilteredInstance()
    {
        $issues = new Issues(array(
            $this->getIssue(array('title' => 'Hello World')),
            $this->getIssue(array('title' => 'oh hai')),
        ));

        $filtered = $issues->filter(function($issue) { return $issue->getTitle() == 'Hello World'; });

        $this->assertCount(1, $filtered);
        $this->assertCount(2, $issues);
    }

    public function testSortReturnsNewSortedInstance()
    {
        $issues = new Issues(array(
            $this->getIssue(array('title' => 'AAA')),
            $this->getIssue(array('title' => 'ZZZ')),
        ));

        $sorted = $issues->sort(function($a, $b) {
            return strcmp($b->getTitle(), $a->getTitle());
        });

        $this->assertSame($issues[0], $sorted[1]);
        $this->assertSame($issues[1], $sorted[0]);
    }

    protected function getIssue(array $overrides = array())
    {
        return new Issue(
            isset($overrides['id'])          ? $overrides['id']          : 1,
            isset($overrides['title'])       ? $overrides['title']       : 't',
            isset($overrides['description']) ? $overrides['description'] : 'd',
            isset($overrides['status'])      ? $overrides['status']      : new Status('open'),
            isset($overrides['dateCreated']) ? $overrides['id']          : new \DateTime,
            isset($overrides['dateUpdated']) ? $overrides['id']          : new \DateTime
        );
    }
}
