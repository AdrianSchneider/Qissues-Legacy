<?php

namespace Qissues\Trackers\Trello;

use Qissues\Trackers\Trello\TrelloMapping;

class TrelloMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testToIssueBasic()
    {
        $date = new \DateTime();
        $trelloCard = array(
            'idShort' => 1,
            'name' => 'Pizza',
            'desc' => 'ham and pineapples',
            'idList' => 8,
            'dateLastActivity' => $date->format('Y-m-d g:ia'),
            'labels' => array()
        );

        $mapper = $this->getMapper(array('lists' => array(array( 'id' => 8, 'name' => 'New'))));
        $issue = $mapper->toIssue($trelloCard);

        $this->assertEquals($trelloCard['idShort'], $issue->getId());
        $this->assertEquals($trelloCard['name'], $issue->getTitle());
        $this->assertEquals($trelloCard['desc'], $issue->getDescription());
        $this->assertEquals('New', $issue->getStatus()->getStatus());
        $this->assertEquals($date->format('u'), $issue->getDateCreated()->format('u'));
        $this->assertEquals($date->format('u'), $issue->getDateUpdated()->format('u'));
        $this->assertEmpty($issue->getLabels());
    }

    public function testToIssueThrowsExceptionIfCannotMapStatus()
    {
        $date = new \DateTime();
        $trelloCard = array(
            'idShort' => 1,
            'name' => 'Pizza',
            'desc' => 'ham and pineapples',
            'idList' => 200,
            'dateLastActivity' => $date->format('Y-m-d g:ia'),
            'labels' => array()
        );

        $this->setExpectedException('LogicException', 'update');

        $mapper = $this->getMapper(array('lists' => array()));
        $issue = $mapper->toIssue($trelloCard);
    }

    public function testToIssueConvertsChecklistsIntoDescription()
    {
        $date = new \DateTime();
        $trelloCard = array(
            'idShort' => 1,
            'name' => 'Pizza',
            'desc' => '',
            'idList' => 8,
            'dateLastActivity' => $date->format('Y-m-d g:ia'),
            'labels' => array(),
            'checklists' => array(
                array(
                    'name' => 'Checklist',
                    'checkItems' => array(
                        array(
                            'name' => "Don't forget the milk",
                            'state' => 'complete'
                        )
                    )
                )
            )
        );

        $mapper = $this->getMapper(array('lists' => array(array( 'id' => 8, 'name' => 'New'))));
        $issue = $mapper->toIssue($trelloCard);

        $this->assertEquals("Checklist\n    [x] Don't forget the milk", $issue->getDescription());
    }

    protected function getMapper(array $board)
    {
        $metadata = $this->getMockBuilder('Qissues\Trackers\Trello\TrelloMetadata')->disableOriginalConstructor()->getMock();
        $metadata
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($board))
        ;

        return new TrelloMapping($metadata);
    }
}
