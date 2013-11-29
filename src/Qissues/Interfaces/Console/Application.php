<?php

namespace Qissues\Interfaces\Console;

use Qissues\Interfaces\Console\Command;
use Qissues\System\ContainerFactory;
use Qissues\Trackers\Shared\Metadata\NullMetadataException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

class Application extends BaseApplication
{
    protected $config = array();
    protected $container;

    /**
     * Retrieve an instance of the IssueTracker
     * @param string|null override tracker to get
     * @return IssueTracker
     */
    public function getTracker($name = '')
    {
        if (!$name) {
            if (isset($this->config['tracker'])) {
                $name = $this->config['tracker'];
            } elseif (isset($this->config['connector'])) {
                $name = $this->config['connector'];
            } else {
                throw new \Qissues\Interfaces\Console\Input\Exception('No configuration found; run "qissues init" first');
            }
        }

        $name = strtolower($name);
        return $this->container->get('tracker.' . $name);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $parser = new Parser();

        // Load Configuration
        $me = posix_getpwuid(getmyuid());
        if (file_exists("$me[dir]/.qissues")) {
            $this->config = $this->mergeConfig($this->config, $parser->parse(file_get_contents("$me[dir]/.qissues")));
        }
        if (file_exists('./.qissues')) {
            $this->config = $this->mergeConfig($this->config, $parser->parse(file_get_contents('./.qissues')));
        }

        $this->registerCommands();
        $this->registerStyles($output);
        $this->registerContainer();

        $name = $this->getCommandName($input);

        if (true === $input->hasParameterOption(array('--ansi'))) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(array('--no-ansi'))) {
            $output->setDecorated(false);
        }

        if (true === $input->hasParameterOption(array('--help', '-h'))) {
            if (!$name) {
                $name = 'help';
                $input = new ArrayInput(array('command' => 'help'));
            } else {
                $this->wantHelps = true;
            }
        }

        if (true === $input->hasParameterOption(array('--no-interaction', '-n'))) {
            $input->setInteractive(false);
        }

        if (function_exists('posix_isatty') && $this->getHelperSet()->has('dialog')) {
            $inputStream = $this->getHelperSet()->get('dialog')->getInputStream();
            if (!posix_isatty($inputStream)) {
                $input->setInteractive(false);
            }
        }

        if (true === $input->hasParameterOption(array('--quiet', '-q'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        } elseif (true === $input->hasParameterOption(array('--verbose', '-v'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        if (true === $input->hasParameterOption(array('--version', '-V'))) {
            $output->writeln($this->getLongVersion());

            return 0;
        }

        if (!$name) {
            $name = 'query';
            $input = new ArrayInput(array('command' => 'query'));
        }

        // the command name MUST be the first element of the input
        $command = $this->find($name);

        $this->runningCommand = $command;
        $statusCode = $command->run($input, $output);
        $this->runningCommand = null;

        return is_numeric($statusCode) ? $statusCode : 0;
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
            ->in(__DIR__.'/Command');
        
        foreach ($finder as $file) {
            $class = "Qissues\\Interfaces\\Console\\Command\\" . basename($file, ".php");
            $this->add(new $class());
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function mergeConfig($a, $b)
    {
        foreach ($b as $key => $value) {
            if (is_array($value)) {
                if (!isset($a[$key])) {
                    $a[$key] = $value;
                } else {
                    $a[$key] = $this->mergeConfig($a[$key], $value);
                }
            } else {
                $a[$key] = $value;
            }
        }

        return $a;
    }

    protected function registerContainer()
    {
        $containerFactory = new ContainerFactory();
        $this->container = $containerFactory->create($this->config);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function renderException($e, $output)
    {
        if ($e instanceof Input\Exception) {
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            return;
        }

        if ($e instanceof NullMetadataException) {
            $output->writeln("<error>This tracker requires metadata; run `qissues refresh` to download.</error>");
            return;
        }

        return parent::renderException($e, $output);
    }
}
