<?php

namespace Qissues\Tests\Model\Tracker\Support;

use Qissues\Domain\Tracker\Support\SupportLevel;

class SupportLevelTest extends \PHPUnit_Framework_TestCase
{
    public function testStartsWithNoSupport()
    {
        $support = new SupportLevel();
        $this->assertFalse($support->supports(SupportLevel::SINGLE));
        $this->assertFalse($support->supports(SupportLevel::MULTIPLE));
        $this->assertFalse($support->supports(SupportLevel::DYNAMIC));
    }

    public function testSetThrowsExceptionIfInvalidLevel()
    {
        $this->setExpectedException('InvalidArgumentException');

        $support = new SupportLevel();
        $support->set('unknown');
    }

    public function testCanAddAndCheck()
    {
        $support = new SupportLevel();
        $support->set('single');

        $this->assertTrue($support->supports(SupportLevel::NONE));
        $this->assertTrue($support->supports(SupportLevel::SINGLE));
    }

    public function testCannotSetSingleAfterMultiple()
    {
        $this->setExpectedException('DomainException');

        $support = new SupportLevel();
        $support->set('multiple');
        $support->set('single');
    }

    public function testCannotSetMultipleAfterSingle()
    {
        $this->setExpectedException('DomainException');

        $support = new SupportLevel();
        $support->set('single');
        $support->set('multiple');
    }

    public function testSupportedWhenAtLeastOneLevel()
    {
        $support = new SupportLevel();
        $support->set('single');
        $this->assertTrue($support->isSupported());
    }

    public function testSupportedWhenNone()
    {
        $support = new SupportLevel();
        $this->assertFalse($support->isSupported());
    }
}
