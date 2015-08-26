<?php

use SocioChat\DAO\SessionDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Message\Lang;
use Zend\Config\Config;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);
/* @var $config Config */
$config = $container->get('config');
$avatarsConfig = $config->uploads->avatars;

$httpAcceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0,
    2) : 'en';
/** @var $lang Lang */
$lang = $container->get('lang')->setLangByCode($httpAcceptLanguage);

function response($code, $message, $image = null)
{
    http_response_code($code);
    echo json_encode(['success' => $code == 200, 'response' => $message, 'image' => $image]);
}

function makeImage($uploadedFile, $dim, $format, $extension, array $coords)
{
    $imagick = new Imagick();
    $imagick->readImage($uploadedFile);
    $imgWidth = $imagick->getimagewidth();
    $imgHeight = $imagick->getimageheight();

    if ($coords['w'] > $imgWidth || $coords['h'] > $imgHeight || $coords['x'] > $imgWidth || $coords['y'] > $imgHeight || $coords['portW'] > $imgWidth || $coords['portH'] > $imgHeight) {
        throw new Exception('Invalid crop data');
    }

    $xFactor = $imgWidth / $coords['portW'];
    $yFactor = $imgHeight / $coords['portH'];

    $imagick->cropimage($xFactor * $coords['w'], $yFactor * $coords['h'], $xFactor * $coords['x'],
        $yFactor * $coords['y']);

    $imgWidth = $imagick->getimagewidth();
    $imgHeight = $imagick->getimageheight();

    if ($imgHeight > $dim || $imgWidth > $dim) {
        if ($imgHeight > $imgWidth) {
            $imagick->resizeimage(0, $dim, imagick::FILTER_CATROM, 1);
        } else {
            $imagick->resizeimage($dim, 0, imagick::FILTER_CATROM, 1);
        }
    }

    $imagick->setImageFormat($format);

    $image = $uploadedFile . $extension;
    if (file_exists($image)) {
        unlink($image);
    }
    $imagick->writeImage($image);
}

$token = isset($_POST['token']) ? $_POST['token'] : null;
$token = SessionDAO::create()->getBySessionId($token);

$img = isset($_FILES['img']) ? $_FILES['img'] : null;
$uploadDir = ROOT . DIRECTORY_SEPARATOR . $avatarsConfig->dir . DIRECTORY_SEPARATOR;
$uploadedName = sha1(time() . basename($img['name']));
$uploadedFile = $uploadDir . $uploadedName;
$allowedMIME = ['image/gif', 'image/png', 'image/jpeg'];

$dim = isset($_POST['dim']) ? $_POST['dim'] : null;
$dim = json_decode($dim, true);

if (!$token->getId() || !$img || $dim === null) {
    $message = $lang->getPhrase('profile.IncorrectRequest');
    response(403, $message);
    return;
}

if (!in_array($img['type'], $allowedMIME)) {
    $message = $lang->getPhrase('profile.IncorrectFileType');
    response(403, $message);
    return;
}

if ($img['size'] > $avatarsConfig->maxsize) {
    $message = $lang->getPhrase('profile.FileExceedsMaxSize') . ' ' . $avatarsConfig->maxsize;
    response(403, $message);
    return;
}

if ($img['error'] != UPLOAD_ERR_OK || !move_uploaded_file($img['tmp_name'], $uploadedFile)) {
    $message = $lang->getPhrase('profile.ErrorUploadingFile');
    response(403, $message);
    return;
}

try {
    makeImage($uploadedFile, $avatarsConfig->thumbdim, 'png', '_t.png', $dim);
    makeImage($uploadedFile, $avatarsConfig->thumbdim * 2, 'png', '_t@2x.png', $dim);
    makeImage($uploadedFile, $avatarsConfig->maxdim, 'jpeg', '.jpg', $dim);
} catch (\Core\BaseException $e) {
    $message = $lang->getPhrase('profile.ErrorProcessingImage') . ': ' . $e->getMessage();
    response(500, $message);
    return;
}

unlink($uploadedFile);

response(200, 'OK', $uploadedName);
