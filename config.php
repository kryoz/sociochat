<?php

$DS = DIRECTORY_SEPARATOR;
define('ROOT', __DIR__);

if (isset($setupErrorHandler)) {
	set_error_handler(
		function ($errno, $errstr, $errfile, $errline) {
			$func = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
			$line = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['line'];
			echo "ERROR calling {$func}() on line $line) : $errstr";
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
