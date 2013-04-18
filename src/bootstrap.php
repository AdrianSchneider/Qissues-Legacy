<?php

if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    die('You must first run composer.');
}

error_reporting(E_ALL);

// XXX allow swapping of ID and command
if (!empty($argv[2]) and preg_match('/^([0-9]+)$/', $argv[1]) and preg_match('/^([a-z]+)$/', $argv[2])) {
    list($argv[1], $argv[2]) = array($argv[2], $argv[1]);
}

// XXX don't require "query" command
if (!empty($argv[1]) and strpos($argv[1], '-') !== false and strpos($argv[1], 'help') === false) {
    array_splice($argv, 1, 0, array('query'));
}

$_SERVER['argv'] = $argv;

$loader = include $file;
$loader->add('Qissues', __DIR__);
