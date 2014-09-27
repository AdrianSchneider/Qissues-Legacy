<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Model\Metadata;
use Qissues\Domain\Shared\Priority;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testSetsPriority()
    {
        $meta = new Metadata( $this->getMock('Qissues\Domain\Model\Status'), new \DateTime, new \DateTime);
        $meta->setPriority($p = new Priority(1, 'major'));

        $this->assertEquals($p, $meta->getPriority());
    }

    public function testOnlySetsPriorityOnce()
    {
        $this->setExpectedException('BadMethodCallException', 'priority');

        $meta = new Metadata( $this->getMock('Qissues\Domain\Model\Status'), new \DateTime, new \DateTime);
        $meta->setPriority(new Priority(1, 'major'));
        $meta->setPriority(new Priority(8, 'minor'));
    }
}
