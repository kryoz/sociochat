<?php

use Core\DI;
use SocioChat\DIBuilder;

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('only internal requests allowed');
}

require_once '../config.php';

$container = DI::get()->container();
DIBuilder::setupNormal($container);

http_response_code(200);
$sid = 'unchanged';
if (!isset($_COOKIE['token']) || isset($_GET['regenerate'])) {
	session_name('token');
	session_start();
	$sid = session_id();
	$sessionHandler = \SocioChat\Session\DBSessionHandler::get();
	$sessionHandler->store($sid, 0);

}
echo json_encode(['token' => $sid]);