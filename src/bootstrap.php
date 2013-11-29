<?php

if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    die('You must first run composer.');
}

error_reporting(E_ALL);

include $file;
