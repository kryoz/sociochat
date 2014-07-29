<?php

use SocioChat\DI;
use SocioChat\DIBuilder;

if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	die('only ajax requests allowed');
}

require_once '../config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);

function curl($url, $postParams, $auth = false) {
	$options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_POST            => true,
		CURLOPT_POSTFIELDS     => http_build_query($postParams),
		CURLOPT_VERBOSE        => 1,
	];

	if ($auth) {
		$options += [
			//CURLOPT_HTTPHEADER      => ['Expect:'],
			CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
            CURLOPT_USERPWD         => DI::get()->getConfig()->music->secret
		];
	}

	$curl = curl_init($url);
	curl_setopt_array($curl, $options);

	$response = curl_exec($curl);
	curl_close($curl);

	return json_decode($response, 1);
}

function response($code, $message)
{
	http_response_code($code);
	echo json_encode($message);
}

$trackId = isset($_GET['track_id']) ? urldecode($_GET['track_id']) : null;
$token = isset($_GET['token']) ? urldecode($_GET['token']) : null;

if (!$trackId) {
	response(400, 'no track_id specified');
	return;
}

if (!$token) {
	$response = curl('http://api.pleer.com/token.php', ['grant_type' => 'client_credentials'], true);
	$token = $response['access_token'];
}

$dao = \SocioChat\DAO\MusicDAO::create()->getByTrackId($trackId);

if (!$dao->getId()) {
	$trackInfo = curl('http://api.pleer.com/resource.php',
		[
			'access_token' => $token,
			'method' => 'tracks_get_info',
			'track_id' => $trackId,
		]
	);

	if (!isset($trackInfo['data'])) {
		response(400, 'invalid track_id specified');
		return;
	}

	$trackInfo = $trackInfo['data'];

	$dao
		->setTrackId($trackId)
		->setArtist($trackInfo['artist'])
		->setSong($trackInfo['track'])
		->setQuality($trackInfo['bitrate']);
	$dao->save();
} else {
	$trackInfo = [
		'artist' => $dao->getArtist(),
		'track' => $dao->getSong(),
		'bitrate' => $dao->getQuality()
	];
}

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

$trackInfo['url'] = 'http://pleer.sociochat.me/'.str_replace('http://', '', $response['url']);

response(200, $trackInfo);