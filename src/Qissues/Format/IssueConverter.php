<?php

namespace Qissues\Format;

use Qissues\Model\NewIssue;

interface IssueConverter
{
    function getFields();
    function toIssue(array $issue);
    function toNewIssue(array $issue);
    function issueToArray(NewIssue $issue);
}
