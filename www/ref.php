<?php

use SocioChat\DAO\NameChangeDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Message\Lang;

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

/** @var SessionDAO $session */
$session = $container->get('session')->read($_COOKIE['token']);
if ($session->getUserId()) {
	header('Location: '.$config->domain->protocol.$config->domain->web, true, 302);
	return;
}

$props = $user->getPropeties();
$props->setKarma($props->getKarma()+1);
$props->save(false);

header('Location: '.$config->domain->protocol.$config->domain->web, true, 302);
return;