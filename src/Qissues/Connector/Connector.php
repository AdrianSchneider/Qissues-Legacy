<?php

namespace Qissues\Connector;

interface Connector
{
    function create(array $issue);
    function update(array $changes, array $issue);

    function changeStatus(array $issue, $newStatus);
    function assign(array $issue, $username);

    function find($id);
    function findAll(array $options);

    function findComments(array $issue);
    function comment(array $issue, $message);
}
