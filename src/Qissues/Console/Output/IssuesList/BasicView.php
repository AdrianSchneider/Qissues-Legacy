<?php

namespace Qissues\Console\Output\IssuesList;

use Qissues\Console\Output\TableRenderer;
use Symfony\Component\Console\Output\OutputInterface;

class BasicView
{
    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    public function render(array $issues, OutputInterface $output, $width, $height)
    {
        $renderIssues = array();
        foreach ($issues as $issue) {
            $renderIssues[] = array(
                'Id'           => $issue['id'],
                'Title'        => strlen($issue['title']) > $width * 0.4
                    ? (substr($issue['title'], 0, $width * 0.4) . '...')
                    : $issue['title'],
                'Status'       => $issue['status'],
                'Type'         => $issue['type'],
                'P'            => $issue['priority'],
                'Date updated' => $issue['updated']->format('Y-m-d g:ia')
            );
        }

        $output->writeln($this->tableRenderer->render($renderIssues, $width));
    }
}
