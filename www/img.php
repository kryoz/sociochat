<?php

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('only internal requests allowed');
}


function ranger($url)
{
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
        'Range: bytes=0-32768'
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}

if (!isset($_GET['url'])) {
    http_response_code(400);
    return;
}

$url = $_GET['url'];
$raw = ranger($url);

if (!$im = imagecreatefromstring($raw)) {
    http_response_code(400);
    return;
}

$width = imagesx($im);
$height = imagesy($im);

if ($width && $height) {
    http_response_code(200);
    echo json_encode(['url' => $url]);
    return;
}

http_response_code(400);
