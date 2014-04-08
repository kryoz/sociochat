<?php

namespace MyApp\OnMessageFilters;

use MyApp\Chain\ChainContainer;
use MyApp\Chain\ChainInterface;
use MyApp\ChatConfig;
use MyApp\Clients\User;
use MyApp\Clients\UserCollection;
use MyApp\MightyLoop;
use MyApp\Response\ErrorResponse;
use MyApp\TSingleton;
use MyApp\Utils\Lang;

class FloodFilter implements ChainInterface
{
	use TSingleton;
	private $otherTimers = [];
	private $pingTimers = [];

	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();

		if (isset($chain->getRequest()['subject']) && $chain->getRequest()['subject'] == 'Ping') {
			return $this->manageRequest($user, $this->pingTimers);
		}

		return $this->manageRequest($user, $this->otherTimers);
	}

	/**
	 * @param User $user
	 * @param array $timers
	 * @return bool
	 */
	private function manageRequest(User $user, array &$timers)
	{
		if ($this->isRequestHot($user, $timers)) {
			$this->respondFloodError($user);
			return false;
		}

		$this->setTimer($user, $timers);
	}

	/**
	 * @param User $user
	 * @param array $timers
	 * @return bool
	 */
	private function isRequestHot(User $user, array $timers)
	{
		return isset($timers[$user->getId()]) ? $timers[$user->getId()] : false;
	}

	/**
	 * @param User $user
	 */
	private function respondFloodError(User $user)
	{
		$response = (new ErrorResponse())
			->setChatId($user->getChatId())
			->setErrors(['flood' => Lang::get()->getPhrase('FloodDetected')]);

		(new UserCollection())
			->setResponse($response)
			->attach($user)
			->notify(false);
	}

	/**
	 * @param User $user
	 * @param array $timers
	 * @return array
	 */
	private function setTimer(User $user, array &$timers)
	{
		$loop = MightyLoop::get()->fetch();
		$timerCallback = function () use ($user, &$timers) {
			unset($timers[$user->getId()]);
		};

		$timer = $loop->addTimer(ChatConfig::get()->getConfig()->floodTimeout, $timerCallback);

		$timers[$user->getId()] = $timer;
	}
}