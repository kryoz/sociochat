<?php

use SocioChat\DI;
use SocioChat\DIBuilder;

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('only internal requests allowed');
}

require_once '../config.php';
$container = DI::get()->container();
DIBuilder::setupNormal($container);

$pageCount = $container->get('config')->music->tracksOnPage;
$hash = isset($_GET['hash']) ? $_GET['hash'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 0;

if (!$hash) {
    http_response_code(400);
    return;
}

$hashes = \SocioChat\DAO\HashDAO::create()->getListByHash($hash, $page*$pageCount, $pageCount);
if (empty($hashes)) {
    http_response_code(200);
    return json_encode([]);
}
$response = [
    'page' => $page,
    'pageCount' => $pageCount,
    'totalCount' => $hashes[0]->getTotalCount(),
];

foreach ($hashes as $hash) {
    $response['hashes'][] = [
        'name'  => $hash->getName(),
        'date'  => $hash->getDateRaw(),
        'message' => $hash->getMessage(),
    ];
}

http_response_code(200);
echo json_encode($response);
