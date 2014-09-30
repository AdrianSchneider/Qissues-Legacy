<?php

namespace Qissues\Domain\Model;

interface Issue
{
    function getId();
    function getTitle();
    function getDescription();
    function getStatus();
    function getPriority();
    function getType();
    function getAssignee();
    function getDateCreated();
    function getDateUpdated();
    function getCommentCount();
    function getLabels();
    function getMilestone();
}
