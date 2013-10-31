<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Console\Input\ExternalIssueFactory;

class ExternalIssueFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateForTracker()
    {
        $parser = $this->getMockBuilder('Qissues\Console\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();

        $issueFactory = new ExternalIssueFactory($editor, $parser);
        $issueFactory->createForTracker($tracker);
    }
}
