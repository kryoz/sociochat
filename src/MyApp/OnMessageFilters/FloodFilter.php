<?php

namespace MyApp\OnMessageFilters;

use MyApp\Chain\ChainContainer;
use MyApp\Chain\ChainInterface;
use MyApp\ChatConfig;
use MyApp\Clients\UserCollection;
use MyApp\MightyLoop;
use MyApp\Response\ErrorResponse;
use MyApp\TSingleton;
use MyApp\Utils\Lang;

class FloodFilter implements ChainInterface
{
	use TSingleton;
	private $userTimers;

	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$loop = MightyLoop::get()->fetch();

		$prevRequest = isset($this->userTimers[$user->getId()]) ? $this->userTimers[$user->getId()] : false;

		if ($prevRequest) {
			$isMsgCase = isset($prevRequest['request']['msg']) && isset($chain->getRequest()['msg']);
			if ($prevRequest['request'] == $chain->getRequest() || $isMsgCase) {
				$response = (new ErrorResponse())
					->setErrors(['flood' => Lang::get()->getPhrase('FloodDetected')])
					->setChatId($user->getChatId());
				(new UserCollection())
					->setResponse($response)
					->attach($user)
					->notify(false);

				return false;
			}

		}

		$timerCallback = function() use ($user) {
			unset($this->userTimers[$user->getId()]);
		};

		$timer = $loop->addTimer(ChatConfig::get()->getConfig()->floodTimeout, $timerCallback);

		$this->userTimers[$user->getId()] = [
			'timer' => $timer,
			'request' => $chain->getRequest()
		];
	}
}