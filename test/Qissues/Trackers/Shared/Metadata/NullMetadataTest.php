<?php

namespace Qissues\Trackers\Shared\Metadata;

use Qissues\Trackers\Shared\Metadata\NullMetadata;

class NullMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfAnyMethodsAreCalled()
    {
        $this->setExpectedException('Qissues\Trackers\Shared\Metadata\NullMetadataException');
        $metadata = new NullMetadata();
        $metadata->showmethemoney();
    }
}
