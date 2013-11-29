<?php

namespace Qissues\Interfaces\Console\Output\View\Issue;

use Qissues\Domain\Model\Issue;

class StandardView
{
    public function render(Issue $issue, $width, $height, array $comments)
    {
        $out = '';
        $out = str_repeat('-', $width) . "\n";

        $title = wordwrap($issue->getTitle(), min($width - 4, 100), "\n", true);

        $out .= sprintf("<comment>%d - %s</comment>\n", $issue->getId(), $title);
        $out .= $this->prepareMeta($issue) . "\n\n";

        $description = wordwrap($issue->getDescription(), min($width - 4, 100), "\n", true);
        foreach (explode("\n", $description) as $row) {
            $out .= "    $row\n";
        }

        $out .= "\n";

        if ($comments) {

            $out .= sprintf("<comment>%d - Comments</comment>\n\n", $issue->getId());

            $multiLine = false;
            foreach ($comments as $comment) {
                if (strpos(trim(wordwrap($comment->getMessage(), $width - 4)), "\n") !== false) {
                    $multiLine = true;
                }
            }

            foreach ($comments as $comment) {
                $date = $comment->getDate()->format('Y-m-d g:ia');
                if ($multiLine) {
                    $out .= sprintf("[%s] <info>%s</info>:\n", $date, $comment->getAuthor());
                    $out .= $this->indent($comment->getMessage(), $width) . "\n";
                } else {
                    $out .= sprintf("[%s] <info>%s</info>: %s\n", $date, $comment->getAuthor(), trim($comment->getMessage()));
                }
            }
        }

        $out .= str_repeat('-', $width) . "\n";
        return $out;
    }

    protected function indent($text, $width)
    {
        $out = '';
        foreach (explode("\n", wordwrap($text, $width - 4)) as $line) {
            $out .= "    $line\n";
        }

        return $out;
    }

    protected function prepareMeta(Issue $issue)
    {
        $fields = array();
        foreach (array('status', 'dateCreated', 'type', 'priority', 'labels', 'assignee') as $field) {
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
