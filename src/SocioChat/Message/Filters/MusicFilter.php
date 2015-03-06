<?php

namespace SocioChat\Message\Filters;

use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Stream\BufferedSink;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\UserCollection;
use SocioChat\DI;
use SocioChat\Message\Msg;
use SocioChat\Message\MsgRaw;
use SocioChat\Response\MusicResponse;


class MusicFilter implements ChainInterface
{
    const MIN_LENGTH = 10;
    /**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain)
    {
		$config = DI::get()->getConfig()->domain;

	    /** @var Client $client */
	    $client = DI::get()->container()->get('httpClient');

	    $onResponse = function (Response $response) use ($chain)
	    {
		    BufferedSink::createPromise($response)->then(function($body) use ($chain) {
			    $logger = DI::get()->getLogger();
			    $logger->info('Got http response: '.$body);
			    if (!$json = json_decode($body, 1)) {
				    return;
			    }

			    $logger->info('JSON decoded');
			    $channelId = $chain->getUser()->getChannelId();
			    /** @var UserCollection $users */
			    $users = DI::get()->getUsers();

			    $response = (new MusicResponse())
				    ->setInfo($json)
				    ->setChannelId($channelId);

			    $users
				    ->setResponse($response)
				    ->notify(false);

			    $logger->info('Sent MusicResponse!');

			    $channel = ChannelsCollection::get()->getChannelById($channelId);
			    $history = $channel->getHistory(0);

			    foreach ($history as $k => $part) {
				    /** @var Msg $msgObj */
				    $msgObj = $part['msg'];
					$string = $msgObj->getMsg($chain->getUser()->getLang());

				    if (!preg_match('~id="music-('.$json['track_id'].')"~u', $string)) {
					    continue;
				    }

				    $string = str_replace(
					    'id="music-'.$json['track_id'].'" data-src=""><span class="glyphicon glyphicon-play-circle">'
				        .'</span> <span class="audio-title">...</span>',

					    'id="music-'.$json['track_id'].'" data-src="'.$json['url'].'" bind-play-click="1">'
					    .'<span class="glyphicon glyphicon-play-circle"></span> '
					    .'<span class="audio-title">'.$json['artist'].' - '.$json['track'].'</span>',

					    $string
				    );

				    $part['msg'] = MsgRaw::create($string);
				    $channel->setRow($k, $part);
			    }
		    });
		};

	    $regexp = '~\b'.$config->protocol.addcslashes($config->web, '.').'/audio\.php\?(?:token=.*)?track_id=(.*)\b~u';

	    if (preg_match($regexp, $chain->getRequest()['msg'], $matches)) {
		    $url = $config->protocol.$config->web.'/audio_player.php?track_id='.$matches[1];
		    DI::get()->getLogger()->info('Sending http request to '.$url);

		    $httpRequest = $client->request(
			    'GET',
			    $url,
			    [
				    'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12'
			    ]
		    );
		    $httpRequest->on('response', $onResponse);
			$httpRequest->on('error', function(\Exception $e) {
				DI::get()->getLogger()->err($e->getMessage().$e->getPrevious()->getMessage());
			});
		    $httpRequest->end();

		    $request = $chain->getRequest();
		    $lang = $chain->getUser()->getLang();

		    $replacement = '<div class="img-thumbnail">'
			    .'<a class="music" href="#" title="'.$lang->getPhrase('Music.PlayTip').'" id="music-$1" data-src="">'
			    .'<span class="glyphicon glyphicon-play-circle"></span> <span class="audio-title">...</span></a></div>';

		    $request['msg'] = $text = preg_replace(
			    $regexp,
			    $replacement,
			    $request['msg']
		    );

		    $chain->setRequest($request);
	    }
    }
}
