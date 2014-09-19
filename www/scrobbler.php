<?php

use Core\DI;
use SocioChat\DIBuilder;

require_once '../config.php';

$container = DI::get()->container();
DIBuilder::setupNormal($container);

function response($code, $message)
{
    http_response_code($code);
    echo json_encode($message);
}

$config = DI::get()->getConfig();
$apiKey = $config->lastfm->apiKey;
$secret = $config->lastfm->secret;

$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;
$artist = isset($_REQUEST['artist']) ? $_REQUEST['artist'] : null;
$track = isset($_REQUEST['track']) ? $_REQUEST['track'] : null;

if (!$token || !$artist || !$track) {
    response(400, ['error' => 'wrong params']);
}

$lfm = new Lastfm\Client($apiKey);
$lfm->getAuthService()->getSession(['token' => $token]);
$lfm->getTrackService()->updateNowPlaying(['artist' => $artist, 'track' => $track]);