<?php

namespace Qissues\Trackers\Trello;

use Qissues\Trackers\Trello\TrelloMetadata;

class TrelloMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testHasList()
    {
        $metadata = new TrelloMetadata(array('lists' => array()));
        $this->assertFalse($metadata->hasList('New'));
    }

    public function testGetListNameById()
    {
        $metadata = new TrelloMetadata(array('lists' => array(array('name' => 'New', 'id' => 5))));
        $this->assertEquals('New', $metadata->getListNameById(5));
    }

    public function testGetListNameByIdThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new TrelloMetadata(array('lists' => array()));
        $metadata->getListNameById(5);
    }

    public function testGetListIdByName()
    {
        $metadata = new TrelloMetadata(array('lists' => array(array('name' => 'New', 'id' => 5))));
        $this->assertEquals(5, $metadata->getListIdByName('New'));
    }

    public function testGetListIdByNameAllowsLazySearch()
    {
        $metadata = new TrelloMetadata(array('lists' => array(array('name' => 'New Car Smell', 'id' => 5))));
        $this->assertEquals(5, $metadata->getListIdByName('new'));
    }

    public function testGetIdListByNameThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'status');
        $metadata = new TrelloMetadata(array('lists' => array()));
        $metadata->getListIdByName('Open');
    }

    public function testGetLabelNameById()
    {
        $metadata = new TrelloMetadata(array('labels' => array('yellow' => 'Ruh Roh')));
        $this->assertEquals('Ruh Roh', $metadata->getLabelNameById('yellow'));
    }

    public function testGetLabelNameByIdThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new TrelloMetadata(array('labels' => array()));
        $metadata->getLabelNameById(5);
    }

    public function testGetLabelIdByName()
    {
        $metadata = new TrelloMetadata(array('labels' => array('red' => 'Fancy')));
        $this->assertEquals('red', $metadata->getLabelIdByName('Fancy'));
    }

    public function testGetLabelIdByNameAllowsLazySearch()
    {
        $metadata = new TrelloMetadata(array('labels' => array('red' => 'Big Fat Bug')));
        $this->assertEquals('red', $metadata->getLabelIdByName('bug'));
    }

    public function testGetLabelIdByNameThrowsExceptionWhenInvalid()
    {
        $this->setExpectedException('LogicException', 'update');
        $metadata = new TrelloMetadata(array('labels' => array()));
        $metadata->getLabelIdByName('Open');
    }

    public function testGetMemberNameById()
    {
        $metadata = new TrelloMetadata(array('members' => array(array('id' => 5, 'username' => 'Bob'))));
        $this->assertEquals('Bob', $metadata->getMemberNameById(5));
    }

    public function testGetMemberNameByIdThrowsExceptionIfNotFound()
    {
        $this->setExpectedException('LogicException', 'not found');
        $metadata = new TrelloMetadata(array('members' => array()));
        $metadata->getMemberNameById(6);
    }

    public function testGetMemberIdByName()
    {
        $metadata = new TrelloMetadata(array('members' => array(array('id' => 5, 'username' => 'Bob'))));
        $this->assertEquals(5, $metadata->getMemberIdByName('Bob'));
    }

    public function testGetMemberIdByNameLazy()
    {
        $metadata = new TrelloMetadata(array('members' => array(array('id' => 5, 'username' => 'Bobby Drop Tables'))));
        $this->assertEquals(5, $metadata->getMemberIdByName('bob'));
    }

    public function testGetMemberIdByNameAlsoSearchesFullName()
    {
        $metadata = new TrelloMetadata(array('members' => array(array('id' => 5, 'username' => 'Bobby Drop Tables', 'fullName' => 'Robert'))));
        $this->assertEquals(5, $metadata->getMemberIdByName('robert'));
    }

    public function testGetMemberIdByNameThrowsExceptionIfNotFoud()
    {
        $this->setExpectedException('LogicException', 'not found');
        $metadata = new TrelloMetadata(array('members' => array()));
        $metadata->getMemberIdByName('robert');
    }
}
