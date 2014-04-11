<?php

namespace SocioChat\Clients;

use SocioChat\ChatConfig;
use SocioChat\MightyLoop;
use SocioChat\TSingleton;

class PendingPrivates
{
	use TSingleton;

	const TTL = 60;
	private $queue = [];

	public function invite(User $userInviter, User $desiredUser, callable $timoutResponse)
	{
		$match = $this->getQueue($userInviter, $desiredUser);

		if (!$match) {
			$match = $this->createInvitation($userInviter, $desiredUser, $timoutResponse);
			return [$match['inviterUserId'], null];
		} elseif ($match['inviterUserId'] != $userInviter->getId()) {
			MightyLoop::get()->fetch()->cancelTimer($match['timer']);
			$this->clearRequest($userInviter, $desiredUser);
			return [null, null];
		}

		return [$match['inviterUserId'], $match['time']];
	}

	public function getTTL()
	{
		return ChatConfig::get()->getConfig()->inviteTimeout;
	}

	protected function getToken(User $userInviter, User $desiredUser)
	{
		return $userInviter->getId() < $desiredUser->getId() ? $userInviter->getId().'.'.$desiredUser->getId() : $desiredUser->getId().'.'.$userInviter->getId();
	}

	protected function getQueue(User $userInviter, User $desiredUser)
	{
		return isset($this->queue[$this->getToken($userInviter, $desiredUser)]) ? $this->queue[$this->getToken($userInviter, $desiredUser)] : false;
	}

	protected function clearRequest(User $userInviter, User $desiredUser)
	{
		unset($this->queue[$this->getToken($userInviter, $desiredUser)]);
	}

	/**
	 * @param User $userInviter
	 * @param User $desiredUser
	 * @param callable $timoutResponse
	 * @return array
	 */
	private function createInvitation(User $userInviter, User $desiredUser, callable $timoutResponse)
	{
		$timer = MightyLoop::get()->fetch()->addTimer(
			$this->getTTL(),
			function() use ($userInviter, $desiredUser, $timoutResponse) {
				$this->clearRequest($userInviter, $desiredUser);
				$timoutResponse($userInviter, $desiredUser);
			}
		);

		$matchUser = [
			'inviterUserId' => $userInviter->getId(),
			'time' => time(),
			'timer' => $timer
		];
		$token = $this->getToken($userInviter, $desiredUser);
		$this->queue[$token] = $matchUser;

		return $matchUser;
	}
}
