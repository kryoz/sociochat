<?php
if (!defined('ROOT')) {
    die('not allowed');
}

use Core\DI;

function curl($url, $postParams, $auth = false)
{
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postParams),
        CURLOPT_VERBOSE => 1,
    ];

    if ($auth) {
        $options += [
            //CURLOPT_HTTPHEADER      => ['Expect:'],
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => DI::get()->getConfig()->music->secret
        ];
    }

    $curl = curl_init($url);
    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, 1);
}

function getToken()
{
    $cache = DI::get()->getCache();

    if (isset($_GET['token'])) {
        $token = urldecode($_GET['token']);
    } elseif (!$cache->has('audioToken')) {
        $response = curl('http://api.pleer.com/token.php', ['grant_type' => 'client_credentials'], true);
        if (!$token = $response['access_token']) {
            die('Abnormal API behavior: unable to receive access token');
        }
        $ttl = isset($response['expires_in']) ? $response['expires_in'] : 3600;

        $cache->set('audioToken', $token, $ttl);
    } else {
        $token = $cache->get('audioToken');
    }

    return $token;
}

function response($code, $message)
{
    http_response_code($code);
    echo json_encode($message);
}
