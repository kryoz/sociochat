<?php

use Core\BaseException;

$DS = DIRECTORY_SEPARATOR;
define('ROOT', __DIR__);

if (!isset($loader)) {
    $loader = require_once ROOT . $DS . 'vendor' . $DS . 'autoload.php';
}

if (isset($setupErrorHandler)) {
    set_error_handler(
        function ($code, $string, $errfile, $errline) {
            throw new BaseException($string, $code);
        },
        E_ALL | E_STRICT
    );
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
         file_put_contents('nohup.out', 'Crushed at '.date('Y-m-d H:i:s'));
    }
});
error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('UTC');

setlocale(LC_CTYPE, "en_US.UTF8");
setlocale(LC_TIME, "en_US.UTF8");

$defaultEncoding = 'UTF-8';
mb_internal_encoding($defaultEncoding);
mb_regex_encoding($defaultEncoding);

