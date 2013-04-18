<?php

namespace Qissues\Command;

use Qissues\Connector\BitBucket;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ViewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('view')
            ->setDescription('View details for an issue')
            ->addArgument('issue', InputArgument::OPTIONAL, 'The issue ID')
            ->addOption('no-comments', null, InputOption::VALUE_NONE, 'Don\'t print comments', null)
            ->addOption('web', 'w', InputOption::VALUE_NONE, 'Open in web browser.', null)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $this->getApplication()->getConnector();
        if (!$issue = $connector->find($this->getIssueId($input))) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        if ($input->getOption('web')) {
            return exec(sprintf(
                'xdg-open %s',
                escapeshellarg($connector->getIssueUrl($issue))
            ));
        }

        list($width, $height) = $this->getApplication()->getTerminalDimensions();
        $issue['title'] = wordwrap($issue['title'], min($width - 4, 100), "\n", true);

        $output->writeln("");
        $output->writeln("<comment>$issue[id] - $issue[title]</comment>");
        $output->writeln(sprintf(
            "  Priority: <info>%s</info> - Kind: <info>%s</info> - Assignee: <info>%s</info>\n",
            $issue['priority_text'],
            $issue['type'],
            $issue['assignee'] ?: 'unassigned'
        ));

        $issue['description'] = wordwrap($issue['description'], min($width - 4, 100), "\n", true);

        foreach (explode("\n", $issue['description']) as $row) {
            $output->writeln($row);
        }

        $output->writeln("");
        if (!$input->getOption('no-comments')) {
            foreach ($connector->findComments($issue) as $comment) {
                $date = $comment['date']->format('Y-m-d g:ia');
                $output->writeln("[$date] <info>$comment[username]</info>: $comment[message]");
            }
        }
    }
}
