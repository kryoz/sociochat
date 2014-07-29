<!doctype html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>SocioChat - Музыка</title>
</head>
<body>
<?php

use SocioChat\DI;
use SocioChat\DIBuilder;

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

$song = isset($_REQUEST['song']) ? urldecode($_REQUEST['song']) : null;
$trackId = isset($_GET['track_id']) ? urldecode($_GET['track_id']) : null;
$token = isset($_GET['token']) ? urldecode($_GET['token']) : null;

if (!$trackId) {
?>
<form method="POST" action="audio.php">
	<input type="text" name="song" placeholder="Введите имя артиста или песни" maxlength="255" value="<?=htmlspecialchars($song)?>" style="width:80%">
	<input type="submit" value="Искать">
</form>
<?php
}

if (!$token) {
	$response = curl('http://api.pleer.com/token.php', ['grant_type' => 'client_credentials'], true);
	$token = $response['access_token'];
}

if ($song) {
	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$response = curl('http://api.pleer.com/resource.php',
		[
			'access_token' => $token,
			'method' => 'tracks_search',
			'result_on_page' => 30,
			'page' => $page,
			'query' => $song
		]
	);
?>
	Найдено записей: <?=$response['count']?>
	<br>
	<?php
	for ($i=1; $i <= ($response['count'] / 30); $i++) {
		echo '<a href="?song='.$song.'&page='.$i.'&token='.$token.'">'.$i.'</a> | ';
	}
	?>
	<table>
		<thead>
			<th>Песня</th>
			<th>Качество (кбит/сек)</th>
		</thead>
	<?php foreach ($response['tracks'] as $id => $trackInfo) { ?>
		<tr>
			<td><a href="?token=<?=urlencode($token)?>&track_id=<?=urlencode($trackInfo['id'])?>"><?=$trackInfo['artist'].' - '.$trackInfo['track']?></td>
			<td><?=$trackInfo['bitrate']?></td>
		</tr>
	<?php } ?>
	</table>
<?php
	return;
}

if ($trackId) {
	$trackInfo = curl('http://api.pleer.com/resource.php',
		[
			'access_token' => $token,
			'method' => 'tracks_get_info',
			'track_id' => $trackId,
		]
	);

	if (!isset($trackInfo['data'])) {
		die('invalid track_id');
	}

	$trackInfo = $trackInfo['data'];

	$response = curl('http://api.pleer.com/resource.php',
		[
			'access_token' => $token,
			'method' => 'tracks_get_download_link',
			'track_id' => $trackId,
			'reason' => 'listen'
		]
	);

	if ($response['success'] == true) {
		$url = 'http://pleer.sociochat.me/'.str_replace('http://', '', $response['url'].'?track_id='.$trackId);

		$dao = \SocioChat\DAO\MusicDAO::create()->getByTrackId($trackId);

		if (!$dao->getId()) {
			$dao
				->setTrackId($trackId)
				->setArtist($trackInfo['artist'])
				->setSong($trackInfo['track'])
				->setQuality($trackInfo['bitrate']);
			$dao->save();
		}

?>
	<table>
		<td><b><?=$dao->getArtist()?></b> - <?=$dao->getSong()?></td>
		<td><?=$dao->getQuality()?> kbit/sec</td>
	</table>
		<br>
	<audio controls autoplay>
		<source src="<?=$url?>" type="audio/mp3" >
		Ваш браузер не поддерживает тег audio!
	</audio>
	<br>
	<input type="text" id="url-container" value="https://sociochat.me/audio.php?track_id=<?=$dao->getTrackId()?>"/>
	<button id="copy-button" data-clipboard-target="url-container" data-clipboard-text="Default clipboard text from attribute" data-copied-hint="Скопировано!" title="Скопировать ссылку в буфер обмена">Скопировать ссылку в буфер обмена</button>
	<br>
	<a href="https://sociochat.me/audio.php">Искать другую композицию</a>
	<script src="js/zeroclipboard.min.js"></script>
	<script>
		var client = new ZeroClipboard( document.getElementById("copy-button") );
	</script>
<?php
	}
}
?>
</body>
</html>