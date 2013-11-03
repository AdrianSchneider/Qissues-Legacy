<?php

namespace Qissues\Console\Output\Issue;

use Qissues\Model\Issue;

class StandardView
{
    public function render(Issue $issue, $width, $height, array $comments)
    {
        $out = '';
        $out = str_repeat('-', $width) . "\n";

        $title = wordwrap($issue['title'], min($width - 4, 100), "\n", true);

        $out .= "<comment>$issue[id] - $title</comment>\n";
        $out .= "  " . $this->prepareMeta($issue) . "\n\n";

        $description = wordwrap($issue['description'], min($width - 4, 100), "\n", true);
        foreach (explode("\n", $description) as $row) {
            $out .= "$row\n";
        }

        $out .= "\n";

        if ($comments) {
            foreach ($comments as $comment) {
                $date = $comment['date']->format('Y-m-d g:ia');
                $out .= "[$date] <info>$comment[author]</info>: $comment[message]\n";
            }
        }

        $out .= str_repeat('-', $width) . "\n";
        return $out;
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
