<?php

use Monolog\Logger;
use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Message\Lang;
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
$httpAcceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
/** @var $lang Lang */
$lang = $container->get('lang')->setLangByCode($httpAcceptLanguage);

function response($code, $message, $image = null)
{
	http_response_code($code);
	echo json_encode(['success' => $code == 200, 'response' => $message, 'image' => $image]);
}

$token = isset($_POST['token']) ? $_POST['token'] : null;
$token = SessionDAO::create()->getBySessionId($token);

$img = isset($_FILES['img']) ? $_FILES['img'] : null;
$uploadDir = ROOT.DIRECTORY_SEPARATOR.$avatarsConfig->dir.DIRECTORY_SEPARATOR;
$uploadedName = sha1(basename($img['name']));
$uploadedFile = $uploadDir.$uploadedName;
$allowedMIME = ['image/gif', 'image/png', 'image/jpeg'];

if (!$token->getId() || !$img) {
	$message = $lang->getPhrase('profile.IncorrectRequest');
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

if (!in_array($img['type'], $allowedMIME)) {
	$message = $lang->getPhrase('profile.IncorrectFileType');
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

if ($img['size'] > $avatarsConfig->maxsize) {
	$message = $lang->getPhrase('profile.FileExceedsMaxSize').' '.$avatarsConfig->maxsize;
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

if ($img['error'] != UPLOAD_ERR_OK || !move_uploaded_file($img['tmp_name'], $uploadedFile)) {
	$message = $lang->getPhrase('profile.ErrorUploadingFile');
	$logger->error($message, $logContext);
	response(403, $message);
	return;
}

try {
	$imagick = new Imagick();
	$imagick->readImage($uploadedFile);

	$imagick->thumbnailImage($avatarsConfig->thumbdim, $avatarsConfig->thumbdim);
	$imagick->setImageFormat('PNG');

	$image = $uploadedFile.'_t.png';
	if (file_exists($image)) {
		unlink($image);
	}
	$imagick->writeImage($image);

	$imagick = new Imagick();
	$imagick->readImage($uploadedFile);

	if ($imagick->getimagewidth() > $avatarsConfig->maxdim || $imagick->getimageheight() > $avatarsConfig->maxdim) {
		$imagick->adaptiveresizeimage($avatarsConfig->maxdim, $avatarsConfig->maxdim , true);
	}
	$imagick->setBackgroundColor(new ImagickPixel('white'));
	$imagick->setImageFormat('jpeg');
	$image = $uploadedFile.'.jpg';
	if (file_exists($image)) {
		unlink($image);
	}
	$imagick->writeImage($image);
}
catch (\Exception $e) {
	$message = $lang->getPhrase('profile.ErrorProcessingImage').': '.$e->getMessage();
	$logger->error($message, $logContext);
	response(500, $message);
	return;
}

unlink($uploadedFile);

response(200, 'OK', $uploadedName);
