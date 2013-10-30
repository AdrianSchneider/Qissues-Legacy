<?php

namespace Qissues\Command;

use Qissues\Model\Number;
use Qissues\Model\IssueTracker;
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
        $tracker = $this->getApplication()->getTracker();
        $number = new Number($this->getIssueId($input));
        if (!$issue = $tracker->lookup($number)) {
            return $output->writeln('<error>Issue not found.</error>');
        }

        if ($input->getOption('web')) {
            return exec(sprintf(
                'xdg-open %s', // TODO config for opener prog
                escapeshellarg($tracker->getIssueUrl($issue))
            ));
        }

        list($width, $height) = $this->getApplication()->getTerminalDimensions();

        $output->writeln(str_repeat('-', $width));

        $title = wordwrap($issue['title'], min($width - 4, 100), "\n", true);

        $output->writeln("<comment>$issue[id] - $title</comment>");
        $output->writeln(sprintf(
            "  Priority: <info>%s</info> - Kind: <info>%s</info> - Assignee: <info>%s</info>\n",
            $issue->getPriority(),
            $issue['type'],
            $issue['assignee'] ?: 'unassigned'
        ));

        $description = wordwrap($issue['description'], min($width - 4, 100), "\n", true);
        foreach (explode("\n", $description) as $row) {
            $output->writeln($row);
        }

        $output->writeln("");
        $this->renderComments($input, $output, $tracker, $number);
        $output->writeln(str_repeat('-', $width));
    }

    private function renderComments(InputInterface $input, OutputInterface $output, IssueTracker $tracker, Number $issue)
    {
        if (!$input->getOption('no-comments')) {
            foreach ($tracker->findComments($issue) as $comment) {
                $date = $comment['date']->format('Y-m-d g:ia');
                $output->writeln("[$date] <info>$comment[author]</info>: $comment[message]");
            }
        }
    }
}
