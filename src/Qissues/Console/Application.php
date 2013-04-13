<?php

namespace Qissues\Console;

use Qissues\Command;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

class Application extends BaseApplication
{
    protected $config;

    public function getConnector($name)
    {
        $class = "Qissues\\Connector\\$name";
        if (!class_exists($class)) {
            throw new \Exception("$name connector not found");
        }

        return new $class($this->config[strtolower($name)]);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $parser = new Parser();
        $this->config = $parser->parse(file_get_contents(__DIR__ . '/../../../.config'));

        $this->registerCommands();
        $this->registerStyles($output);

        return parent::doRun($input, $output);
    }

    protected function registerStyles($output)
    {
        $styles = array(
            'message' => new OutputFormatterStyle(null, null),
            'p5' => new OutputFormatterStyle('red', 'black', array('bold')),
            'p4' => new OutputFormatterStyle('red', 'black'),
            'p3' => new OutputFormatterStyle('white', 'black'),
            'p2' => new OutputFormatterStyle('blue', 'black'),
            'p1' => new OutputFormatterStyle('green', 'black')
        );

        foreach ($styles as $styleName => $styleDefinition) {
            $output->getFormatter()->setStyle($styleName, $styleDefinition);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function registerCommands()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->name('*Command.php')
            ->notName('Command.php')
            ->in(__DIR__.'/../Command');
        
        foreach ($finder as $file) {
            $class = "Qissues\\Command\\" . basename($file, ".php");
            $this->add(new $class());
        }
    }

    public function getConfig()
    {
        return $this->config;
    }
}
