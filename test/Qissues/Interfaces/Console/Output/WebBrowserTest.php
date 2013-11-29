<?php

namespace Qissues\Tests\Console\Output;

use Qissues\Interfaces\Console\Output\WebBrowser;

class WebBrowserTest extends \PHPUnit_Framework_TestCase
{
    public function testUseSelectedBrowser()
    {
        $shell = $this->getShellAsserting('firefox index.html');
        $browser = new WebBrowser($shell, 'firefox');
        $browser->open('index.html');
    }

    public function testDefaultsToXdgOpenOnLinux()
    {
        $browser = new WebBrowser($this->getShellAsserting('xdg-open index.html', 'Linux'));
        $browser->open('index.html');
    }

    public function testDefaultsToOpenOnMac()
    {
        $browser = new WebBrowser($this->getShellAsserting('open index.html', 'Darwin'));
        $browser->open('index.html');
    }

    public function testThrowsExceptionWhenunsure()
    {
        $this->setExpectedException('RunTimeException', 'not supported');
        $browser = new WebBrowser($this->getShellAsserting(null, 'SayWhat'));
        $browser->open('index.html');
    }

    protected function getShellAsserting($command = null, $uname = null)
    {
        $index = 0;
        $shell = $this->getMock('Qissues\System\Shell\Shell');

        if ($uname) {
            $shell
                ->expects($this->at($index++))
                ->method('run')
                ->with('uname')
                ->will($this->returnValue($uname))
            ;
        }
        if ($command) {
            $shell
                ->expects($this->at($index++))
                ->method('escape')
                ->with('index.html')
                ->will($this->returnValue('index.html'))
            ;
            $shell
                ->expects($this->at($index++))
                ->method('run')
                ->with($this->equalTo($command))
            ;
        }

        return $shell;
    }
}
