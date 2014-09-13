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

session_name('token');

if (!$sid || isset($_GET['regenerate'])) {
	session_regenerate_id(true);
}
session_start();
$sid = session_id();
$sessionHandler = DI::get()->getSession();

if (!$sessionHandler->read($sid)) {
	$sessionHandler->store($sid, 0);
}

setcookie(session_name(), $sid, time()+$config->session->lifetime, '/', '.'.$config->domain->web, true);
http_response_code(200);
echo json_encode(['token' => $sid]);