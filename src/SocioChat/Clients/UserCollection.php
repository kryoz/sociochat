<?php

namespace SocioChat\Clients;

use SocioChat\DAO\PropertiesDAO;
use SocioChat\DI;
use SocioChat\Log;
use SocioChat\Response\MessageResponse;
use SocioChat\Response\Response;
use SocioChat\TSingleton;

class UserCollection
{
	use TSingleton;

	/**
	 * @var User[]
	 */
	private $users = [];

	/**
	 * @var Response
	 */
	private $response = null;

	public function attach(User $user)
	{
		$this->users[$user->getId()] = $user;
		return $this;
	}

	public function detach(User $user)
	{
		if (isset($this->users[$user->getId()])) {
			DI::get()->container()->get('logger')->info("Detach userId = {$user->getId()}", [__CLASS__]);
			$user->close();
			unset($this->users[$user->getId()]);
		}

		return $this;
	}

	public function notify($log = true)
	{
		$response = $this->getResponse();
		$this->handleLog($response, $log);

		foreach ($this->users as $user) {
			/* @var $user User */
			if ($response->getChatId() == $user->getChatId()) {
				// Filter responses from banned users
				$saveGuests = $response->getGuests();

				if (!$response->getFrom() || ($response->getFrom() && !$user->getBlacklist()->isBanned($response->getFrom()->getId()))) {
					$this->banInfo($response, $user);
					$user->update($response);
				}

				$response->setGuestsRaw($saveGuests);
			}
		}
	}

	protected function handleLog(Response $response, $log)
	{
		$isMessage = $response instanceof MessageResponse;
		$saveGuests = $response->getGuests();

		if ($isMessage && $log) {
			/* @var $response MessageResponse */
			$lastMsgId = ChannelsCollection::get()->pushToHistory($response);
			$response->setLastMsgId($lastMsgId);
		}

		$response->setGuestsRaw($saveGuests);
	}

	public function setResponse(Response $response)
	{
		$this->response = $response;
		return $this;
	}

	/**
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * @param $chatId
	 * @return User[]
	 */
	public function getUsersByChatId($chatId)
	{
		$chatUsers = null;
		foreach ($this->users as $user) {
			if ($user->getChatId() == $chatId) {
				$chatUsers[] = $user;
			}
		}
		return $chatUsers;
	}

	/**
	 * @param $connectionId
	 * @return User|null
	 */
	public function getClientByConnectionId($connectionId)
	{
		foreach ($this->users as $user) {
			if ($user->getConnectionId() == $connectionId) {
				return $user;
			}
		}
	}

	/**
	 * @param $userId
	 * @return User|null
	 */
	public function getClientById($userId)
	{
		if (isset($this->users[$userId])) {
			return $this->users[$userId];
		}
	}

	/**
	 * @param $userName
	 * @return User|null
	 */
	public function getClientByName($userName)
	{
		foreach ($this->users as $user) {
			if ($user->getProperties()->getName() == $userName) {
				return $user;
			}
		}
	}

	public function getClientsCount($chatId)
	{
		$counter = 0;
		foreach ($this->users as $user) {
			if ($user->getChatId() == $chatId) {
				$counter++;
			}
		}
		return $counter;
	}

	public function getTotalCount()
	{
		return count($this->users);
	}

	private function banInfo(Response $response, User $user)
	{
		if ($newGuests = $response->getGuests()) {
			foreach ($newGuests as &$guest) {
				if ($user->getBlacklist()->isBanned($guest[PropertiesDAO::USER_ID])) {
					$guest += ['banned' => $user->getId()];
				}
			}
			$response->setGuestsRaw($newGuests);
		}
	}
}
