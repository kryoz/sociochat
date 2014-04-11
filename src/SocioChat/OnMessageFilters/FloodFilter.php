<?php

namespace SocioChat\OnMessageFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\ChatConfig;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\MightyLoop;
use SocioChat\Response\ErrorResponse;
use SocioChat\TSingleton;
use SocioChat\Utils\Lang;

class FloodFilter implements ChainInterface
{
	use TSingleton;
	private $otherTimers = [];
	private $pingTimers = [];

	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();

		if (isset($chain->getRequest()['subject']) && $chain->getRequest()['subject'] == 'Ping') {
			return $this->manageRequest($user, $this->pingTimers, false);
		}

		return $this->manageRequest($user, $this->otherTimers);
	}

	/**
	 * @param User $user
	 * @param array $timers
	 * @param bool $doResponse
	 * @return bool
	 */
	private function manageRequest(User $user, array &$timers, $doResponse = true)
	{
		if ($this->isRequestHot($user, $timers)) {
			if ($doResponse) {
				$this->respondFloodError($user);
			}
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