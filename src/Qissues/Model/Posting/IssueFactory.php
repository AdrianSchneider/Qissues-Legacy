<?php

namespace Qissues\Model\Posting;

use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\Strategy\InputStrategy;

class IssueFactory
{
    public function create(IssueTracker $tracker, InputStrategy $strategy)
    {
        return $strategy->create($tracker);
    }

    public function update(IssueTracker $tracker, InputStrategy $strategy, Issue $existing)
    {
        return $strategy->update($tracker, $existing);
    }
}
