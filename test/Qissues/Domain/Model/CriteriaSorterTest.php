<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Model\CriteriaSorter;

class CriteriaSorterTest extends \PHPUnit_Framework_TestCase
{
    public function testNotImplementedYet()
    {
        $sorter = new CriteriaSorter(new SearchCriteria());

        $issues = array(
            $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock()
        );

        $this->assertEquals(0, $sorter($issues[0], $issues[1]));
    }
}
