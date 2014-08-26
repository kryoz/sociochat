<?php

use SocioChat\DAO\MusicDAO;
use Core\DI;
use SocioChat\DIBuilder;

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('only ajax requests allowed');
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
	$response = curl('http://api.pleer.com/resource.php',
		[
			'access_token' => $token,
			'method' => 'tracks_get_download_link',
			'track_id' => $trackId,
			'reason' => 'listen'
		]
	);

	if (!$response['success']) {
		response(400, 'invalid track_id specified');
		return;
	}

	$trackInfo = curl('http://api.pleer.com/resource.php',
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
	$dao->save();
} else {
	$trackInfo = [
		'artist' => $dao->getArtist(),
		'track' => $dao->getSong(),
		'bitrate' => $dao->getQuality()
	];
}

$trackInfo['url'] = 'http://pleer.sociochat.me/'.str_replace('http://', '', $dao->getUrl().'?track_id='.$trackId);

response(200, $trackInfo);