<?php

use SocioChat\DAO\MusicDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;

require_once '../config.php';
require_once 'pages/audio/common.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);

$song = isset($_REQUEST['song']) ? urldecode($_REQUEST['song']) : null;
$trackId = isset($_GET['track_id']) ? urldecode($_GET['track_id']) : null;
$token = getToken();

require_once "pages/audio/head.php";

if (!$trackId) {
    require_once "pages/audio/search_form.php";
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
    require_once "pages/audio/search_result_list.php";
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
        die('invalid service response, try request again');
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
        $url = 'http://pleer.sociochat.me/' . str_replace('http://', '', $response['url'] . '?track_id=' . $trackId);

        $dao = MusicDAO::create()->getByTrackId($trackId);

        if (!$dao->getId()) {
            $dao
                ->setTrackId($trackId)
                ->setArtist($trackInfo['artist'])
                ->setSong($trackInfo['track'])
                ->setQuality($trackInfo['bitrate'])
                ->setUrl($response['url']);
            $dao->save();
        }

        require_once "pages/audio/listen.php";
    }
}

require_once "pages/audio/footer.php";