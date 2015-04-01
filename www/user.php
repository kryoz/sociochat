<?php

use SocioChat\DAO\NameChangeDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Message\Lang;

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('only internal requests allowed');
}

if (!isset($_COOKIE['token'])) {
	die('Unauthorized');
}

require_once '../config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
$httpAcceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
	? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
$lang = $container->get('lang')->setLangByCode($httpAcceptLanguage);
/* @var $lang Lang */
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
$note = $owner->getUserNotes()->getNote($user->getId());
$total = $props->getTotal();

$dtF = new DateTime("@0");
$dtT = new DateTime("@".$props->getOnlineCount());

$names = [];
foreach (NameChangeDAO::create()->getHistoryByUserId($user->getId()) as $name) {
	/** @var NameChangeDAO $name */
	$names[] = $name->getName();
}

$response = [
	'id'    => $user->getId(),
    'name' => $props->getName(),
    'avatar' => $props->getAvatarImg() ? $avatarDir.$props->getAvatarImg() : null,
    'tim' => $props->getTim()->getName(),
	'sex' => $props->getSex()->getName(),
	'birth' => $props->getAge() ?: $lang->getPhrase('NotSpecified'),
	'note' => $note ?: '',
	'karma' => $props->getKarma(),
	'dateRegister' => $user->getDateRegister(),
	'onlineTime' => $dtF->diff($dtT)->format('%a дней %h часов %i минут'),
	'wordRating' => $props->getWordRating() . '-й из '. $total,
	'rudeRating' => $props->getRudeRating() . '-й из '. $total,
	'musicRating' => $props->getMusicRating() . '-й из '. $total,
	'names' => implode(', ', $names),
];

$userActions = new \SocioChat\Permissions\UserActions($owner);
$response['allowed'] = $userActions->getAllowed($user);

http_response_code(200);
echo json_encode($response);
