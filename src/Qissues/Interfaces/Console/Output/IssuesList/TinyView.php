<?php

namespace Qissues\Interfaces\Console\Output\IssuesList;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Tracker\Support\FeatureSet;
use Qissues\Interfaces\Console\Output\SpacedTableRenderer;

class TinyView
{
    protected $renderer;
    protected $priorities;
    protected $colors;

    public function __construct(SpacedTableRenderer $renderer, array $priorities, array $colors)
    {
        $this->renderer = $renderer;
        $this->priorities = $priorities;
        $this->colors = $colors;
    }

    public function render(array $issues, FeatureSet $features, $width, $height)
    {
        $maxLength = 0;
        foreach ($issues as $issue) {
            if (strlen($issue['id']) > $maxLength) {
                $maxLength = strlen($issue['id']);
            }
        }

        $allowedSize = $width
            - 4          // icons
            - $maxLength // number area
            - 1          // space
        ;

        foreach ($issues as $issue) {
            $this->renderer->addRow($row = array(
                $this->getPriority($issue, $features),
                $this->getIcons($issue, $features),
                strval($issue->getId()),
                strlen($issue['title']) > $allowedSize
                    ? (substr($issue['title'], 0, $allowedSize - 3) . '...')
                    : $issue['title']
            ));
        }

        return $this->renderer->render();
    }

    protected function getPriority(Issue $issue, FeatureSet $features)
    {
        if ($issue->getPriority()) {
            return $this->priorities[$issue->getPriority()->getPriority()];
        }

        return $this->priorities[3];
    }

    protected function getIcons(Issue $issue, FeatureSet $features)
    {
        if ($issue->getType()) {
            return $this->first($issue->getType());
        }

        if ($issue->getLabels()) {
            return implode(',', array_map(array($this, 'first'), $issue->getLabels()));
        }
    }

    protected function first($obj)
    {
        $name = (string)$obj;
        if (isset($this->colors[$name])) {
            return sprintf(
                '<%s>%s</%s>', 
                $this->colors[$name],
                $name[0],
                $this->colors[$name]
            );
        }

        return $name[0];
    }
}
