<?php

use Core\DI;
use SocioChat\DIBuilder;

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('only internal requests allowed');
}

require_once '../config.php';

$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');

//ini_set('session.use_cookies', 'On');
//session_name('token');
//session_set_cookie_params(time()+$config->session->lifetime, '/', '.'.$config->domain->web, true);
//session_start();

$sid = '';

if (isset($_COOKIE['token'])) {
	$sid = $_COOKIE['token'];
}

if (!$sid || isset($_GET['regenerate'])) {
	//session_regenerate_id(true);
	$sid = bin2hex(openssl_random_pseudo_bytes(16));
}

//$sid = session_id();
$sessionHandler = DI::get()->getSession();

if (!$sessionHandler->read($sid)) {
	$sessionHandler->store($sid, 0);
}

//setcookie('token', $sid, time()+$config->session->lifetime, '/', '.'.$config->domain->web, true);
http_response_code(200);
echo json_encode(
	[
		'token' => $sid,
		'ttl' => time()+$config->session->lifetime,
		'isSecure' => true,
	]
);
