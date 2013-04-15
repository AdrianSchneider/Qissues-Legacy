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
            ->addOption('no-comments', null, InputOption::VALUE_NONE, 'Don\'t print comments', null)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($input->getArgument('issue'))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        $issue['title'] = wordwrap($issue['title'], min($width - 4, 100), "\n", true);

        $output->writeln("");
        $output->writeln("<comment>$issue[id] - $issue[title]</comment>");
        $output->writeln("  Priority: <info>$issue[priority_text]</info> - Kind: <info>$issue[type]</info>\n");

        $issue['description'] = wordwrap($issue['description'], min($width - 4, 100), "\n", true);

        foreach (explode("\n", $issue['description']) as $row) {
            $output->writeln($row);
        }

        $output->writeln("");
        if (!$input->getOption('no-comments')) {
            foreach (array_reverse($connector->findComments($issue)) as $comment) {
                $date = $comment['date']->format('Y-m-d g:ia');
                $output->writeln("[$date] <info>$comment[username]</info>: $comment[content]");
            }
        }
    }
}
