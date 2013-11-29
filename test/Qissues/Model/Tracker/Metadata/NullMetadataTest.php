<?php

namespace Qissues\Application\Tracker\Metadata;

use Qissues\Application\Tracker\Metadata\NullMetadata;

class NullMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfAnyMethodsAreCalled()
    {
        $this->setExpectedException('Qissues\Application\Tracker\Metadata\NullMetadataException');
        $metadata = new NullMetadata();
        $metadata->showmethemoney();
    }
}
