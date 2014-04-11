<?php
use SocioChat\ChatConfig;

$DS = DIRECTORY_SEPARATOR;
define('ROOT', __DIR__);

if (!isset($loader)) {
	$loader = require_once ROOT.$DS.'vendor'.$DS.'autoload.php';
	$loader->register();
}

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Moscow');

setlocale(LC_CTYPE, "en_US.UTF8");
setlocale(LC_TIME, "en_US.UTF8");

$defaultEncoding = 'UTF-8';
mb_internal_encoding($defaultEncoding);
mb_regex_encoding($defaultEncoding);

function CustomErrorHandler($errno, $errstr, $errfile, $errline)
{
	echo "ErrorHandler: $errfile line $errline: $errstr\n";
	return true;
}

set_error_handler('CustomErrorHandler');

ini_set("session.gc_maxlifetime", ChatConfig::get()->getConfig()->session->lifetime);