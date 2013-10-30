<?php

namespace Qissues\Tests\Model;

use Qissues\Model\Issue;

class IssueTest extends \PHPUnit_Framework_TestCase
{
    public function testReadArrayAccess()
    {
        $issue = new Issue(1, 'title', 'desc');
        $this->assertEquals(1, $issue['id']);
    }

    public function testWriteArrayAccessThrowsException()
    {
        $issue = new Issue(1, 'title', 'desc');
        $this->setExpectedException('BadMethodCallException');
        $issue['id'] = 5;
    }
}
