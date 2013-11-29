<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Interfaces\Console\Input\ReportManager;

class ReportManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testUseSpecifiedReport()
    {
        $manager = new ReportManager(array('bugs' => array('bugreport')));
        $report = $manager->findReport($this->getInput('bugs'));
        $this->assertEquals(array('bugreport'), $report);
    }

    public function testThrowsErrorWhenSpecifiedReportDoesNotExist()
    {
        $this->setExpectedException('Qissues\Interfaces\Console\Input\Exception');

        $manager = new ReportManager(array());
        $manager->findReport($this->getInput('features'));
    }

    public function testUseDefaultReportWhenAvailable()
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input
            ->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function($arg) {
                return null;
            }))
        ;

        $manager = new ReportManager(array('default' => array('allthebugs')));
        $report = $manager->findReport($input);
        $this->assertEquals(array('allthebugs'), $report);
    }

    public function testUseNoReportWhenAnyCriteriaAlreadyExists()
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input
            ->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function($arg) {
                if ($arg == 'report') {
                    return null;
                }
                if (in_array($arg, array('keyword', 'status', 'assignee', 'priority', 'type', 'mine', 'ids'))) {
                    return 'spaghetti';
                }
            }))
        ;

        $manager = new ReportManager(array('default' => array('allthebugs')));
        $report = $manager->findReport($input);

        $this->assertNull($report);
    }

    public function testUseNoReportWhenNoneConfigured()
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $manager = new ReportManager(array());
        $report = $manager->findReport($input);

        $this->assertNull($report);
    }

    protected function getInput($returningValue)
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('report')
            ->will($this->returnValue($returningValue))
        ;

        return $input;
    }
}
