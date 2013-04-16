<?php

if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    die('You must first run composer.');
}

// XXX
if (!empty($argv[2]) and preg_match('/^([0-9]+)$/', $argv[1]) and preg_match('/^([a-z]+)$/', $argv[2])) {
    list($argv[1], $argv[2]) = array($argv[2], $argv[1]);
    $_SERVER['argv'] = $argv;
}

$loader = include $file;
$loader->add('Qissues', __DIR__);
