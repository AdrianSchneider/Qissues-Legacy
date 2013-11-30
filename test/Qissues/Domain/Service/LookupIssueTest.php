<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Service\LookupIssue;

class LookupIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $obj = 'real issue';
        $number = new Number(5);

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('lookup')
            ->with($number)
            ->will($this->returnValue($obj));
        ;

        $service = new LookupIssue($repository);
        $this->assertSame($obj, $service($number));
    }
}
