<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Interfaces\Console\Input\ExternalFileEditor;

class ExternalFileEditorTest extends \PHPUnit_Framework_TestCase
{
    public function testUseSelectedEditor()
    {
        $editor = new ExternalFileEditor(
            $this->getMock('Qissues\Interfaces\Console\Shell\Shell'),
            $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock(),
            'vim'
        );

        $this->assertEquals('vim', $editor->getEditor());
    }

    public function testUseVisualAsFallback()
    {
        putenv('EDITOR=nano');

        $editor = new ExternalFileEditor(
            $this->getMock('Qissues\Interfaces\Console\Shell\Shell'),
            $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock()
        );

        $this->assertEquals('nano', $editor->getEditor());
    }

    public function testUseEditorAsSeconaryFallback()
    {
        putenv('EDITOR=');
        putenv('EDITOR=vi');

        $editor = new ExternalFileEditor(
            $this->getMock('Qissues\Interfaces\Console\Shell\Shell'),
            $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock()
        );

        $this->assertEquals('vi', $editor->getEditor());
    }

    public function testThrowExceptionifCannotFindEditor()
    {
        putenv('EDITOR=');
        putenv('EDITOR=');

        $this->setExpectedException('BadMethodCallException');

        new ExternalFileEditor(
            $this->getMock('Qissues\Interfaces\Console\Shell\Shell'),
            $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock()
        );
    }

    public function testReadsFromExternalEditor()
    {
        $template = '';
        $input = 'hello world';

        $editor = new ExternalFileEditor(
            $shell = $this->getMock('Qissues\Interfaces\Console\Shell\Shell'),
            $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock(),
            '.vim',
            '.qissues'
        );

        $filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($this->isType('string'))
            ->will($this->returnValue($template))
        ;

        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with($this->isType('string'))
            ->will($this->returnValue($input))
        ;
        $filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($this->isType('string'))
        ;

        $shell
            ->expects($this->once())
            ->method('run')
        ;

        $this->assertEquals($input, $editor->getEdited($template));
    }
}
