<?php

use SocioChat\DAO\TmpSessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('only internal requests allowed');
}

require_once '../config.php';

$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');


$token = null;

if (isset($_COOKIE['token'])) {
	$token = $_COOKIE['token'];
}

if (!$token || $token == 'null' || isset($_GET['regenerate'])) {
	$token = bin2hex(openssl_random_pseudo_bytes(16));
}

$sessionHandler = DI::get()->getSession();

if (!$sessionHandler->read($token)) {
	$tmpSession = TmpSessionDAO::create();
	$tmpSession
		->setSessionId($token)
		->save();
}

http_response_code(200);
echo json_encode(
	[
		'token' => $token,
		'ttl' => time()+$config->session->lifetime,
		'isSecure' => $config->domain->protocol == 'https://',
	]
);
