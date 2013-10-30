<?php

namespace Qissues\Model;

interface IssueTrackerSupport
{
    const NONE     = 0;
    const SINGLE   = 1;
    const MULTIPLE = 2;
    const DYNAMIC  = 4;

    function getMilestoneSupport();
    function getPrioritySupport();
    function getTypeSupport();
    function getComponentSupport();
    function getCustomFieldSupport();
    function getStatusSupport();
}
