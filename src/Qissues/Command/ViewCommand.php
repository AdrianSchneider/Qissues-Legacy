<?php

namespace Qissues\Command;

use Qissues\Connector\BitBucket;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class ViewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('view')
            ->setDescription('View details for an issue')
            ->addArgument('issue', InputArgument::REQUIRED, 'The issue ID')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = new BitBucket();
        if (!$issue = $repo->find($input->getArgument('issue'))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $output->writeln("\n\n");
        $output->writeln("<question></question>");
        $output->writeln("<comment>Issue:</comment> <info>$issue[local_id]. $issue[title]</info>");
        $output->writeln("<comment>Description:</comment>");

        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        $issue['content'] = wordwrap($issue['content'], $width - 4, "\n", true);

        foreach (explode("\n", $issue['content']) as $row) {
            $output->writeln("    <info>$row</info>");
        }

        $output->writeln("\n\n");
    }
}
