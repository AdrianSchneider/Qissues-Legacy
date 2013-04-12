<?php

namespace Qissues\Connector;

interface Connector
{
    function find($id);
    function findAll();
}
