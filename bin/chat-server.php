<?php

$setupErrorHandler = 1;
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';

try {
    $app = new \SocioChat\ServerStarter();
    $app->run();
} catch (\Core\BaseException $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
