<?php

$DS = DIRECTORY_SEPARATOR;
define('ROOT', __DIR__);

if (isset($setupErrorHandler)) {
	set_error_handler(
		function ($errno, $errstr, $errfile, $errline) {
			$debugInfo = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			echo "ERROR fired! $errstr\n";
			print_r($debugInfo);
			return true;
		}
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

if (!isset($loader)) {
	$loader = require_once ROOT.$DS.'vendor'.$DS.'autoload.php';
}

basicSetup();
