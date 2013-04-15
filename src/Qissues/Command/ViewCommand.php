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
        $connector = $this->getApplication()->getConnector('BitBucket');
        if (!$issue = $connector->find($input->getArgument('issue'))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        $output->writeln("<comment>$issue[local_id] - $issue[title]</comment>");
        $output->writeln("  Priority: <info>$issue[prioritytext]</info> - Kind: <info>$issue[kind]</info>\n");

        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        $issue['content'] = wordwrap($issue['content'], $width - 4, "\n", true);

        foreach (explode("\n", $issue['content']) as $row) {
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
