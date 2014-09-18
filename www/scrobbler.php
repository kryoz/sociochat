<?php

use Core\DI;
use SocioChat\DIBuilder;

//if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
//    die('only internal requests allowed');
//}

require_once '../config.php';

$container = DI::get()->container();
DIBuilder::setupNormal($container);

function response($code, $message)
{
    http_response_code($code);
    echo json_encode($message);
}

$apiKey = isset($_REQUEST['apikey']) ? $_REQUEST['apikey'] : null;
$artist = isset($_REQUEST['artist']) ? $_REQUEST['artist'] : null;
$track = isset($_REQUEST['track']) ? $_REQUEST['track'] : null;

if (!$apiKey || !$artist || !$track) {
    response(400, ['error' => 'wrong params']);
}
$lfm = new Lastfm\Client($apiKey);
$lfm->getTrackService()->updateNowPlaying(['artist' => $artist, 'track' => $track]);