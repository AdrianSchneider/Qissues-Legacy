<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\IssueTrackerSupport as Support;

class BitBucketSupport implements Support
{
    public function getMilestoneSupport()
    {
        return Support::NONE;
    }

    function getPrioritySupport()
    {
        return Support::SINGLE;
    }

    function getTypeSupport()
    {
        return Support::SINGLE;
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
