<?php

// The script handles audio stream and caches info in DB
// Pleer.com allows streaming only to the host which made request
// So we have to be a sort of proxy. audio.sociochat.me is confnginx caching proxy

use SocioChat\DAO\MusicDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!(($_SERVER['REMOTE_ADDR'] == '::ffff:46.101.136.244') || $isAjax)) {
    die('only internal requests allowed, got '.$_SERVER['REMOTE_ADDR']);
}

require_once '../config.php';
require_once 'pages/audio/common.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);

$trackId = isset($_GET['track_id']) ? urldecode($_GET['track_id']) : null;
$token = getToken();

if (!$trackId) {
    response(400, 'no track_id specified');
    return;
}

$dao = MusicDAO::create()->getByTrackId($trackId);

if (!$dao->getId()) {
    $response = curl('http://api.pleer.com/index.php',
        [
            'access_token' => $token,
            'method' => 'tracks_get_download_link',
            'track_id' => $trackId,
            'reason' => 'listen'
        ]
    );

    if (!$response['success']) {
        response(400, 'invalid track_id = '.$trackId.' specified or unexpected response ('.print_r($response, 1).')');
        return;
    }

    $trackInfo = curl('http://api.pleer.com/index.php',
        [
            'access_token' => $token,
            'method' => 'tracks_get_info',
            'track_id' => $trackId,
        ]
    );

    if (!isset($trackInfo['data'])) {
        response(400, 'invalid service response, try request again');
        return;
    }

    $trackInfo = $trackInfo['data'];

    $dao
        ->setTrackId($trackId)
        ->setArtist($trackInfo['artist'])
        ->setSong($trackInfo['track'])
        ->setQuality($trackInfo['bitrate'])
        ->setUrl($response['url']);

    try {
        $dao->save();
    } catch (PDOException $e) {
        /* */
    }

} else {
    $trackInfo = [
        'artist' => $dao->getArtist(),
        'track' => $dao->getSong(),
        'bitrate' => $dao->getQuality()
    ];
}

$trackInfo['url'] = DI::get()->getConfig()->domain->protocol.'audio.sociochat.me/' . str_replace('http://', '', $dao->getUrl() . '?track_id=' . $trackId);
$trackInfo['track_id'] = $trackId;

response(200, $trackInfo);