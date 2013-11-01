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
        $output->writeln("  " . $this->prepareMeta($issue) . "\n");

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

    protected function prepareMeta(Issue $issue)
    {
        $fields = array();
        foreach (array('dateCreated', 'type', 'priority', 'labels', 'assignee') as $field) {
            $method = 'get' . ucfirst($field);
            $value = $issue->$method();

            if ($field == 'assignee' and !$value) {
                $value = 'unassigned';
            }
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d');
            }

            if ($value) {
                if (is_array($value)) {
                    $value = implode(',', array_map('strval', $value));
                }
                $fields[] = ucfirst($field) . ': <info>' . $value . '</info>';
            }
        }

        return implode(' - ', $fields);
    }
}
