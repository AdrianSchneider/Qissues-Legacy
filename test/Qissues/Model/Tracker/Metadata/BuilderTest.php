<?php

namespace Qissues\Application\Tracker\Metadata;

use Qissues\Application\Tracker\Metadata\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $stored = array('a' => 'b');
        $key = 'trello-' . getcwd();

        $storage = $this->getMock('Qissues\Application\Tracker\Metadata\Storage');
        $storage
            ->expects($this->once())
            ->method('exists')
            ->with($key)
            ->will($this->returnValue(true))
        ;
        $storage
            ->expects($this->once())
            ->method('get')
            ->with('trello-' . getcwd())
            ->will($this->returnValue($stored))
        ;

        $builder = new Builder($storage, 'trello', __NAMESPACE__ . '\\Test');
        $metadata = $builder->build();

        $this->assertEquals($stored, $metadata->getData());
        $this->assertInstanceOf(__NAMESPACE__ . '\\Test', $metadata);
    }

    public function testReturnNullMetadataWhenUnavailable()
    {
        $key = 'trello-' . getcwd();

        $storage = $this->getMock('Qissues\Application\Tracker\Metadata\Storage');
        $storage
            ->expects($this->once())
            ->method('exists')
            ->with($key)
            ->will($this->returnValue(false))
        ;

        $builder = new Builder($storage, 'trello', 'anything');
        $metadata = $builder->build();

        $this->assertInstanceOf('Qissues\Application\Tracker\Metadata\NullMetadata', $metadata);
    }
}

class Test
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
