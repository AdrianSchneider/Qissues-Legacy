<?php

namespace Qissues\Console\Output\IssuesList;

use Qissues\Model\Tracker\Support\FeatureSet;

class TinyView
{
    public function render(array $issues, FeatureSet $features, $width, $height)
    {
        $priorities = array(
            5 => '▲',
            4 => '▴',
            3 => '-',
            2 => '▾',
            1 => '▼'
        );
        $types = array(
            'bug' => '<p5>B</p5>'
        );

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

        $out = '';
        foreach ($issues as $issue) {
            $out .= sprintf(
                '%s %s <comment>%s%d</comment> <message>%s</message>',
                $priorities[$issue['priority']->getPriority()],
                $issue['type'] == 'bug' ? $types['bug'] : ' ',
                str_repeat(' ', $maxLength - strlen($issue['id'])),
                $issue['id'],
                strlen($issue['title']) > $allowedSize
                    ? (substr($issue['title'], 0, $allowedSize - 3) . '...')
                    : $issue['title']
            );
        }

        return $out;
    }
}
