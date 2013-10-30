<?php

namespace Qissues\Format;

interface IssueConverter
{
    function toIssue(array $issue);
    function toNewIssue(array $issue);
    function issueToArray(NewIssue $issue);
}
