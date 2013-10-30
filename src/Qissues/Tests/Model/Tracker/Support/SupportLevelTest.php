<?php

namespace Qissues\Tests\Model\Tracker\Support;

use Qissues\Model\Tracker\Support\SupportLevel;

class SupportLevelTest extends \PHPUnit_Framework_TestCase
{
    public function testStartsWithNoSupport()
    {
        $support = new SupportLevel();
        $this->assertFalse($support->supports(SupportLevel::SINGLE));
        $this->assertFalse($support->supports(SupportLevel::MULTIPLE));
        $this->assertFalse($support->supports(SupportLevel::DYNAMIC));
    }

    public function testCanAddAndCheck()
    {
        $support = new SupportLevel();
        $support->setSingle();

        $this->assertTrue($support->supports(SupportLevel::NONE));
        $this->assertTrue($support->supports(SupportLevel::SINGLE));
    }

    public function testCannotSetSingleAfterMultiple()
    {
        $support = new SupportLevel();
        $support->setMultiple();

        $this->setExpectedException('DomainException');
        $support->setSingle();
    }

    public function testCannotSetMultipleAfterSingle()
    {
        $support = new SupportLevel();
        $support->setSingle();

        $this->setExpectedException('DomainException');
        $support->setMultiple();
    }

    public function testSupportedWhenAtLeastOneLevel()
    {
        $support = new SupportLevel();
        $support->setSingle();
        $this->assertTrue($support->isSupported());
    }

    public function testSupportedWhenNone()
    {
        $support = new SupportLevel();
        $this->assertFalse($support->isSupported());
    }
}
