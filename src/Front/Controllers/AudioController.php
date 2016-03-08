<?php

namespace Front\Controllers;


use Core\Cache\Cache;
use Core\Memcache\Wrapper;
use Guzzle\Http\Exception\CurlException;
use PDOException;
use Silex\Application;
use SocioChat\DAO\MusicDAO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AudioController extends BaseController
{
    public function index($trackId)
    {
        $app = $this->app;
        $trackId = urldecode($trackId);

        try {
            $token = $this->getToken();
        } catch (CurlException $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        if (!$trackId) {
            return new JsonResponse('no track_id specified', 400);
        }

        $dao = MusicDAO::create()->getByTrackId($trackId);

        if (!$dao->getId()) {
            $response = $this->curl('http://api.pleer.com/index.php',
                [
                    'access_token' => $token,
                    'method' => 'tracks_get_download_link',
                    'track_id' => $trackId,
                    'reason' => 'listen'
                ]
            );

            if (!$response['success']) {
                return new JsonResponse(
                    'Invalid track_id = '.$trackId.' specified or unexpected response ('.print_r($response, 1).')',
                    400
                );
            }

            $trackInfo = $this->curl('http://api.pleer.com/index.php',
                [
                    'access_token' => $token,
                    'method' => 'tracks_get_info',
                    'track_id' => $trackId,
                ]
            );

            if (!isset($trackInfo['data'])) {
                return new JsonResponse('invalid service response, try request again', 400);
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

        $trackInfo['url'] = $app['config']->domain->protocol
            .$app['config']->music->proxyServer.'/'
            .str_replace('http://', '', $dao->getUrl() . '?track_id=' . $trackId);
        $trackInfo['track_id'] = $trackId;

        return new JsonResponse($trackInfo, 200);
    }

    public function listAction($song, $page, Application $app)
    {
        $pageCount = $app['config']->music->tracksOnPage;
        $song = urldecode($song);

        try {
            $token = $this->getToken();
        } catch (CurlException $e) {
            return new JsonResponse($e->getMessage(), 400);
        }

        if (!$song) {
            return new JsonResponse();
        }

        $response = $this->curl('http://api.pleer.com/index.php',
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
        return new JsonResponse($response);
    }

    private function curl($url, $postParams, $auth = false)
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
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $this->app['config']->music->secret
            ];
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, $options);
        $this->app['logger']->debug('Connecting '.$url.' with options: '.print_r($options, 1));
        $response = curl_exec($curl);
        curl_close($curl);

        $this->app['logger']->debug($response);

        return json_decode($response, 1) ?: [];
    }

    private function getToken()
    {
        /** @var Wrapper $cache */
        $cache = $this->app['memcache'];

        if (!$cache->isStored('audioToken')) {
            $response = $this->curl('http://api.pleer.com/token.php', ['grant_type' => 'client_credentials'], true);

            if (!$token = $response['access_token']) {
                throw new CurlException('Abnormal API behavior: unable to receive access token');
            }
            $ttl = isset($response['expires_in']) ? $response['expires_in'] : 3600;

            $cache->set('audioToken', $token, $ttl);
        } else {
            $cache->get('audioToken', $token);
            $this->app['logger']->debug('Get Pleer token from cache');
        }

        $this->app['logger']->debug('Token = '.$token);
        return $token;
    }
}