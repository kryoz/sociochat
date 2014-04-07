<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Tests', __DIR__ . '/helpers');
$loader->register();

require_once __DIR__.'/../config.php';
