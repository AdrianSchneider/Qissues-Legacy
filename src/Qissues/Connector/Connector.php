<?php

namespace Qissues\Connector;

interface Connector
{
    function create(array $issue);

    function find($id);
    function findAll();
}
