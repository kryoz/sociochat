<?php

use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;

require_once '../config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$config = $container->get('config');

$userId = isset($_GET['id']) ? (int) $_GET['id'] : '';

if (!$userId) {
	header('Location: '.$config->domain->protocol.$config->domain->web, true, 302);
    return;
}

$user = \SocioChat\DAO\UserDAO::create()->getById($userId);
if (!$user->getId()) {
	header('Location: '.$config->domain->protocol.$config->domain->web, true, 302);
	return;
}

$token = isset($_COOKIE['token']) ? $_COOKIE['token'] : null;
if ($token) {
	/** @var SessionDAO $session */
	$session = $container->get('session')->read($token);
	if ($session->getUserId()) {
		header('Location: '.$config->domain->protocol.$config->domain->web, true, 302);
		return;
	}
}

setcookie('refUserId', $user->getId(), time()+180);

header('Location: '.$config->domain->protocol.$config->domain->web, true, 302);
return;