<?php

namespace Qissues\Console\Output\IssuesList;

use Qissues\Console\Output\TableRenderer;
use Symfony\Component\Console\Output\OutputInterface;

class DetailedView
{
    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    public function render(array $issues, OutputInterface $output, $width, $height)
    {
        $renderIssues = array();
        foreach ($issues as $issue) {
            $title = $issue->getTitle();
            $renderIssues[] = array(
                '#'            => $issue->getId(),
                'Title'        => strlen($title) > $width * 0.4
                    ? (substr($title, 0, $width * 0.4) . '...')
                    : $title,
                'Status'       => $issue['status'],
                'Type'         => $issue['type'],
                'Priority'     => $issue['priority'],
                'Assignee'     => $issue['assignee'],
                'Date Created' => $issue->getDateCreated()->format('Y-m-d g:ia'),
                'Date updated' => $issue->getDateUpdated()->format('Y-m-d g:ia'),
                'Comments'     => $issue->getCommentCount()
            );
        }

        $output->writeln($this->tableRenderer->render($renderIssues, $width));
    }
}
