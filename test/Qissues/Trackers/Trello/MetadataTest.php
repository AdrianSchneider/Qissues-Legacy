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

    public function testGetListIdByNameAllowsLazySearch()
    {
        $metadata = new Metadata(array('lists' => array(array('name' => 'New Car Smell', 'id' => 5))));
        $this->assertEquals(5, $metadata->getListIdByName('new'));
    }

    public function testGetIdListByNameThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new Metadata(array('lists' => array()));
        $metadata->getListIdByName('Open');
    }

    public function testGetLabelNameById()
    {
        $metadata = new Metadata(array('labels' => array('yellow' => 'Ruh Roh')));
        $this->assertEquals('Ruh Roh', $metadata->getLabelNameById('yellow'));
    }

    public function testGetLabelNameByIdThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new Metadata(array('labels' => array()));
        $metadata->getLabelNameById(5);
    }

    public function testGetLabelIdByName()
    {
        $metadata = new Metadata(array('labels' => array('red' => 'Fancy')));
        $this->assertEquals('red', $metadata->getLabelIdByName('Fancy'));
    }

    public function testGetLabelIdByNameAllowsLazySearch()
    {
        $metadata = new Metadata(array('labels' => array('red' => 'Big Fat Bug')));
        $this->assertEquals('red', $metadata->getLabelIdByName('bug'));
    }

    public function testGetLabelIdByNameThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new Metadata(array('labels' => array()));
        $metadata->getLabelIdByName('Open');
    }
}
