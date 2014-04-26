<?php

use Monolog\Logger;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use Zend\Config\Config;

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
/* @var $config Config */
$config = $container->get('config');
$avatarsConfig = $config->uploads->avatars;
/** @var $logger Logger  */
$logger = $container->get('logger');
$logContext = ['UPLOAD'];

function response($code, $message)
{
	http_response_code($code);
	echo json_encode(['success' => $code == 200, 'response' => $message]);
}

$token = isset($_POST['token']) ? $_POST['token'] : null;
$token = SessionDAO::create()->getBySessionId($token);

$img = isset($_FILES['img']) ? $_FILES['img'] : null;
$uploadDir = ROOT.DIRECTORY_SEPARATOR.$avatarsConfig->dir.DIRECTORY_SEPARATOR;
$uploadedName = sha1(basename($img['name']));
$uploadedFile = $uploadDir.$uploadedName;
$allowedMIME = ['image/gif', 'image/png', 'image/jpeg'];


if (!$token->getId() || !$img) {
	$message = 'Incorrect request';
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

if (!in_array($img['type'], $allowedMIME)) {
	$message = 'Incorrect file type';
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

if ($img['size'] > $avatarsConfig->maxsize) {
	$message = 'File exceeds allowed max size of '.$avatarsConfig->maxsize.' bytes having '.$img['size'];
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

if ($img['error'] != UPLOAD_ERR_OK || !move_uploaded_file($img['tmp_name'], $uploadedFile)) {
	$message = 'Error uploading file '.$uploadedFile;
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

try {
	$imagick = new Imagick();
	$imagick->readImage($uploadedFile);
	$imagick->thumbnailImage($avatarsConfig->thumbdim, $avatarsConfig->thumbdim);
	$imagick->setImageFormat('PNG');
	$imagick->writeImage($uploadedFile.'_t.png');

	$imagick = new Imagick();
	$imagick->readImage($uploadedFile);
	$imagick->thumbnailImage($avatarsConfig->maxdim, $avatarsConfig->maxdim);
	$imagick->setImageFormat('JPEG');
	$imagick->writeImage($uploadedFile.'.jpg');
}
catch (\Exception $e) {
	$message = 'Error when transforming image:'. $e->getMessage();
	$logger->error($message, $logContext);
	response(500, $message);
	return;
}

unlink($uploadedFile);

$properties = PropertiesDAO::create()->getByUserId($token->getUserId());
$properties
	->setAvatarImg($uploadedName)
	->save();

response(200, 'ok');
