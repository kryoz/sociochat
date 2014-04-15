<?php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orno\Di\Container;
use SocioChat\ChatConfig;
use SocioChat\DB;
use SocioChat\DI;
use React\EventLoop\Factory as Loop;
use SocioChat\Message\Dictionary;
use SocioChat\Message\Lang;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;

$DS = DIRECTORY_SEPARATOR;
define('ROOT', __DIR__);

if (!isset($loader)) {
	$loader = require_once ROOT.$DS.'vendor'.$DS.'autoload.php';
	$loader->register();
}

function CustomErrorHandler($errno, $errstr, $errfile, $errline)
{
	echo "ErrorHandler: $errfile line $errline: $errstr\n";
	return true;
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

	set_error_handler('CustomErrorHandler');
}

function dependencyInjectionSetup(Container $container)
{
	$container->add('config', function () {
			$DS = DIRECTORY_SEPARATOR;
			$confPath = ROOT.$DS.'conf'.$DS;
			$reader = new Ini();
			$config = new Config($reader->fromFile($confPath . 'default.ini'));
			if (file_exists($confPath . 'local.ini')) {
				$config->merge(new Config($reader->fromFile($confPath . 'local.ini')));
			}

			return $config;
		},
		true
	);

	$container->add('eventloop', function () {
			return Loop::create();
		},
		true
	);

	$container->add('logger', function () use ($container) {
			$logger = new Logger('Chat');
			$type = $container->get('config')->logger ?: STDOUT;
			$logger->pushHandler(new StreamHandler($type));
			return $logger;
		},
		true
	);

	$container->add('db', DB::class, true)
		->withArgument('config');

	$container->add('dictionary', Dictionary::class, true)
		->withArgument('logger');

	$container->add('lang', Lang::class)
		->withArgument('dictionary');
}

basicSetup();

$container = DI::get()->container();
dependencyInjectionSetup($container);

$config = $container->get('config');

ini_set("session.gc_maxlifetime", $config->session->lifetime);
