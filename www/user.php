<?php

use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('only internal requests allowed');
}

if (!isset($_COOKIE['token'])) {
	die('Unauthorized');
}

require_once '../config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);


$userId = isset($_GET['id']) ? (int) $_GET['id'] : '';

if (!$userId) {
    http_response_code(400);
    return;
}

/** @var SessionDAO $session */
$session = $container->get('session')->read($_COOKIE['token']);
if (!$session->getUserId()) {
	http_response_code(400);
	return json_encode(['error' => 'Unauthorized']);
}
$owner = \SocioChat\DAO\UserDAO::create()->getById($session->getUserId());

$user = \SocioChat\DAO\UserDAO::create()->getById($userId);
if (empty($user)) {
    http_response_code(400);
    return json_encode(['error' => 'No user found']);
}
$props = $user->getPropeties();
$avatarDir = DI::get()->getConfig()->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR;

$response = [
	'id'    => $user->getId(),
    'name' => $props->getName(),
    'avatar' => $props->getAvatarImg() ? $avatarDir.$props->getAvatarImg() : null,
    'tim' => $props->getTim()->getName(),
	'sex' => $props->getSex()->getName(),
	'birth' => $props->getBirthday(),
	'note' => $owner->getUserNotes()->getNote($user->getId())
];

$userActions = new \SocioChat\Permissions\UserActions($owner);
$response['allowed'] = $userActions->getAllowed($user->getId());

http_response_code(200);
echo json_encode($response);
