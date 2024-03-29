<?php

namespace Qissues\Interfaces\Console\Output\View\IssuesList;

use Qissues\Domain\Model\Response\Issues;
use Qissues\Interfaces\Console\Output\Renderer\TableRenderer;
use Qissues\Application\Tracker\Support\FeatureSet;

class BasicView
{
    protected $tableRenderer;

    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    public function render(Issues $issues, FeatureSet $features, $width, $height)
    {
        $renderIssues = array();
        foreach ($issues as $issue) {
            $renderIssues[] = array(
                'Id'           => $issue->getId(),
                'Title'        => strlen($issue->getTitle()) > $width * 0.4
                    ? (substr($issue->getTitle(), 0, $width * 0.4) . '...')
                    : $issue->getTitle(),
                'Status'       => $issue->getStatus(),
                'Date updated' => $issue->getDateUpdated()->format('Y-m-d g:ia')
            );
        }

        return $this->tableRenderer->render($renderIssues, $width);
    }
}
