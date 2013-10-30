<?php

namespace Qissues\Console\Output;

use Qissues\Model\Issue;
use Symfony\Component\Console\Output\OutputInterface;

class SingleView
{
    public function render(Issue $issue, OutputInterface $output, $width, $height, array $comments)
    {
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

        if ($comments) {
            foreach ($comments as $comment) {
                $date = $comment['date']->format('Y-m-d g:ia');
                $output->writeln("[$date] <info>$comment[author]</info>: $comment[message]");
            }
        }

        $output->writeln(str_repeat('-', $width));
    }
}
