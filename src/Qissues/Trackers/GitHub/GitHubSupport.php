<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\IssueTrackerSupport as Support;

class GitHubSupport implements Support
{
    public function getMilestoneSupport()
    {
        return Support::SINGLE | Support::DYNAMIC;
    }

    function getPrioritySupport()
    {
        return Support::NONE;
    }

    function getTypeSupport()
    {
        return Support::MULTIPLE | Support::DYNAMIC;
    }

    function getComponentSupport()
    {
        return Support::NONE;
    }

    function getCustomFieldSupport()
    {
        return Support::NONE;
    }

    function getStatusSupport()
    {
        return Suport::SINGLE;
    }
}
