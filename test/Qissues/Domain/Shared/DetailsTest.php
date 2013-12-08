<?php

namespace Qissues\Domain\Shared;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;

class DetailsTest extends \PHPUnit_Framework_TestCase
{
    public function testSatisfiedWhenFieldsMatch()
    {
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('a'),
            new ExpectedDetail('b')
        ));

        $details = new Details(array('a' => 1, 'b' => 2));

        $this->assertTrue($details->satisfy($expectations));
    }

    public function testNotSatisfiedWhenNotAllFieldsPresent()
    {
        $expectations = new ExpectedDetails(array(new ExpectedDetail('c')));
        $details = new Details(array('a' => 1));

        $this->assertFalse($details->satisfy($expectations));
        $this->assertEquals(array("Required field 'c' was missing"), $details->getViolations());
    }

    public function testNotSatisfiedWhenFieldNotAllowed()
    {
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('color', true, '', array('red', 'blue'))
        ));

        $details = new Details(array('color' => 'green'));

        $this->assertFalse($details->satisfy($expectations));
        $this->assertEquals(array("color only accepts one of [red, blue]"), $details->getViolations());
    }

    public function testSatisfiedWhenFieldFuzzyMatches()
    {
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('color', true, '', array('cherry red', 'deep blue'))
        ));

        $details = new Details(array('color' => 'red'));

        $this->assertTrue($details->satisfy($expectations));
    }
}
