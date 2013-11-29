<?php

namespace Qissues\Interfaces\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a new .qissues command')
            ->setDefinition(array(
                new InputArgument('tracker', InputArgument::OPTIONAL, 'The name of the tracker to use', null)
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$name = $this->getTracker($input, $output)) {
            $output->writeln("<error>Enter a tracker</error>");
            return 1;
        }

        if (!$this->getApplication()->getContainer()->has($service = "tracker.$name")) {
            $output->writeln("<error>Invalid tracker specified</error>");
            return 1;
        }

        $initializer = $this->get('system.initializer');
        $initializer->initialize($name);

        $output->writeln("A new <info>.qissues</info> has been created. Edit it accordingly");
        $output->writeln("For more information, see doc/trackers/$name.md");
    }

    protected function getTracker(InputInterface $input, OutputInterface $output)
    {
        if ($tracker = $input->getArgument('tracker')) {
            return $tracker;
        }

        $dialog = $this->getApplication()->getHelperSet()->get('dialog');
        $trackers = implode(', ', $this->getTrackers());
        $output->writeln('Supported trackers: ' . $trackers);
        return $dialog->ask($output, 'Name of Tracker: ');
    }

    protected function getTrackers()
    {
        $trackers = array();
        foreach ($this->getApplication()->getContainer()->getServiceIds() as $id) {
            $matches = null;
            if (preg_match('/^tracker\.([a-z]+)$/', $id, $matches)) {
                $trackers[] = $matches[1];
            }
        }

        return $trackers;
    }

}
