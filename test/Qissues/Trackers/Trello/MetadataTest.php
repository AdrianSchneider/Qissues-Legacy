<?php

namespace Qissues\Trackers\Trello;

use Qissues\Trackers\Trello\Metadata;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testHasList()
    {
        $metadata = new Metadata(array('lists' => array()));
        $this->assertFalse($metadata->hasList('New'));
    }

    public function testGetListNameById()
    {
        $metadata = new Metadata(array('lists' => array(array('name' => 'New', 'id' => 5))));
        $this->assertEquals('New', $metadata->getListNameById(5));
    }

    public function testGetListNameByIdThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new Metadata(array('lists' => array()));
        $metadata->getListNameById(5);
    }

    public function testGetListIdByName()
    {
        $metadata = new Metadata(array('lists' => array(array('name' => 'New', 'id' => 5))));
        $this->assertEquals(5, $metadata->getListIdByName('New'));
    }

    public function testGetIdListByNameThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new Metadata(array('lists' => array()));
        $metadata->getListIdByName('Open');
    }
}
