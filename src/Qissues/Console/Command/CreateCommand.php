<?php

namespace Qissues\Console\Command;

use Qissues\Connector\Connector;
use Qissues\Input\TemplatedInput;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new issue')
            ->setDefinition(array(
                new InputOption('strategy', null, InputOption::VALUE_OPTIONAL, 'Specify an input strategy')
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker = $this->getApplication()->getTracker();
        $repository = $tracker->getRepository();

        if (!$strategy = $this->getStrategy($input)) {
            $output->writeln("<error>Invalid issue creation strategy specified</error>");
            return 1;
        }

        $strategy->init($input, $output, $this->getApplication());
        $number = $repository->persist($strategy->createNew($tracker));

        $output->writeln("Issue <info>#$number</info> has been created");
    }

    protected function getStrategy(InputInterFace $input)
    {
        $selected = $input->getOption('strategy') ?: $this->getParameter('console.input.default_strategy');
        $strategy = sprintf('console.input.issue_strategy.%s', $selected);

        if (!$this->getApplication()->getContainer()->has($strategy)) {
            return;
        }

        return $this->get($strategy);
    }
}
