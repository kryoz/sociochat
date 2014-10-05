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
        }
        , E_ALL | E_STRICT
    );
}

function basicSetup()
{
    error_reporting(E_ALL | E_STRICT);

    date_default_timezone_set('Europe/Moscow');

    setlocale(LC_CTYPE, "en_US.UTF8");
    setlocale(LC_TIME, "en_US.UTF8");

    $defaultEncoding = 'UTF-8';
    mb_internal_encoding($defaultEncoding);
    mb_regex_encoding($defaultEncoding);
}

basicSetup();
