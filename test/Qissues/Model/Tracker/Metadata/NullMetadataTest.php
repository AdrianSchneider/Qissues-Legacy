<?php

namespace Qissues\Model\Tracker\Metadata;

use Qissues\Model\Tracker\Metadata\NullMetadata;

class NullMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfAnyMethodsAreCalled()
    {
        $this->setExpectedException('Qissues\Model\Tracker\Metadata\NullMetadataException');
        $metadata = new NullMetadata();
        $metadata->showmethemoney();
    }
}
