<?php

namespace Qissues\Tests\Console\Output;

use Qissues\Console\Output\SpacedTableRenderer;

class SpacedTableRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testRendersColumnsSpaced()
    {
        $renderer = new SpacedTableRenderer();
        $renderer->addRow(array('a', 'b', 'c'));

        $this->assertEquals('a b c', $renderer->render());
    }

    public function testRendersColumnsInConstrainedWidths()
    {
        $renderer = new SpacedTableRenderer();
        $renderer->addRow(array('a', 'b', 'c'));
        $renderer->addRow(array('a1', 'b', 'c2'));

        $this->assertEquals("a  b c \na1 b c2", $renderer->render());
    }

    public function testCalculatesLengthsWithoutTags()
    {
        $renderer = new SpacedTableRenderer();
        $renderer->addRow(array('a', 'b', 'c'));
        $renderer->addRow(array('<error>a</error>', 'b', 'c'));

        $output = $renderer->render();

        $this->assertEquals('a b c', substr($output, 0, 5));
    }

    public function testHandlesUnicodeLenghts()
    {
        $renderer = new SpacedTableRenderer();
        $renderer->addRow(array('▼', 'b'));
        $renderer->addRow(array('a', 'b'));
        $this->assertEquals("▼ b\na b", $renderer->render());
    }
}
