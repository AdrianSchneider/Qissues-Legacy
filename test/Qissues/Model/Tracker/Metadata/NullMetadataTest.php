<?php

namespace Qissues\Domain\Tracker\Metadata;

use Qissues\Domain\Tracker\Metadata\NullMetadata;

class NullMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfAnyMethodsAreCalled()
    {
        $this->setExpectedException('Qissues\Domain\Tracker\Metadata\NullMetadataException');
        $metadata = new NullMetadata();
        $metadata->showmethemoney();
    }
}
