<?php

namespace Qissues\Trackers\Jira;

use Qissues\Trackers\Jira\JiraMetadata;

class JiraMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBasics()
    {
        $metadata = new JiraMetadata(array('id' => 1, 'key' => 'KY'));

        $this->assertEquals(1, $metadata->getId());
        $this->assertEquals('KY', $metadata->getKey());
    }

    public function testGetTypeIdByName()
    {
        $metadata = new JiraMetadata(array('types' => array(array('id' => 1, 'name' => 'New'))));
        $this->assertEquals(1, $metadata->getTypeIdByName('New'));
    }

    public function getGetTypeIdByNameFuzzySearches()
    {
        $metadata = new JiraMetadata(array('types' => array(array('id' => 1, 'name' => 'New Tasks'))));
        $this->assertEquals(1, $metadata->getTypeIdByName('new'));
    }

    public function testGetTypeIdByNameThrowsExceptionWhenNotFound()
    {
        $this->setExpectedException('Exception', 'not found');
        $metadata = new JiraMetadata(array('types' => array()));
        $metadata->getTypeIdByName('anything');
    }
}
