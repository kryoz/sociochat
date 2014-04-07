<?php

namespace MyApp\Clients;

use MyApp\DAO\PropertiesDAO;
use MyApp\Log;
use MyApp\Response\MessageResponse;
use MyApp\Response\Response;
use MyApp\TSingleton;

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
		$this->users[] = $user;
		return $this;
	}

	public function detach(User $user)
	{
		foreach ($this->users as $n => $chatUser) {
			if ($chatUser->getId() == $user->getId()) {
				Log::get()->fetch()->info("Detach userId = {$user->getId()}", [__CLASS__]);
				$user->close();
				unset($this->users[$n]);
			}
		}

		return $this;
	}

	public function notify($log = true)
	{
		/* @var $user User */
		$response = $this->getResponse();
		$isMessage = $response instanceof MessageResponse;
		$saveGuests = $response->getGuests();

		if ($isMessage && $log) {
			/* @var $response MessageResponse */
			$lastMsgId = ChatsCollection::get()->addLine($response);
			$response->setLastMsgId($lastMsgId);
		}

		$response->setGuestsRaw($saveGuests);

		foreach ($this->users as $user) {
			if ($response->getChatId() == $user->getChatId()) {

				/* @var $response Response */
				// Фильтрация ответов от забаненных
				$saveGuests = $response->getGuests();

				if ($response->getFrom() && !$user->getBlacklist()->isBanned($response->getFrom()->getId())) {
					$this->banInfo($response, $user);
					$user->update($response);
				} elseif (!$response->getFrom()) {
					$this->banInfo($response, $user);
					$user->update($response);
				}

				$response->setGuestsRaw($saveGuests);
			}
		}
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
		$chatUsers = [];
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
		foreach ($this->users as $user) {
			if ($user->getId() == $userId) {
				return $user;
			}
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
