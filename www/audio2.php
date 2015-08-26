<?php
// The script handles searching music via pleer.com api
use SocioChat\DI;
use SocioChat\DIBuilder;

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('only internal requests allowed');
}

require_once '../config.php';
require_once 'pages/audio/common.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);

$pageCount = $container->get('config')->music->tracksOnPage;
$song = isset($_REQUEST['song']) ? urldecode($_REQUEST['song']) : null;
$token = getToken();

if (!$song) {
    return;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$response = curl('http://api.pleer.com/index.php',
    [
        'access_token' => $token,
        'method' => 'tracks_search',
        'result_on_page' => $pageCount,
        'page' => $page,
        'query' => $song
    ]
);

$response += [
    'page' => $page,
    'pageCount' => $pageCount,
];
echo json_encode($response);
