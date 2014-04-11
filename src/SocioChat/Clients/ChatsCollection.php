<?php

namespace SocioChat\Clients;


use SocioChat\Response\Response;
use SocioChat\TSingleton;

class ChatsCollection
{
	use TSingleton;
	/**
	 * @var ChatRoom[]
	 */
	private $chatRooms;

	/**
	 * @param string $roomId
	 * @return $this
	 */
	public function fetchRoom($roomId)
	{
		if (!isset($this->chatRooms[$roomId])) {
			$this->chatRooms[$roomId] = new ChatRoom();
		}
		return $this;
	}

	/**
	 * @param User $user
	 */
	public function clean(User $user)
	{
		$roomId = $user->getChatId();
		if (UserCollection::get()->getClientsCount($roomId) == 0) {
			if (isset($this->chatRooms[$roomId]) && $user->isInPrivateChat()) {
				unset($this->chatRooms[$roomId]);
				$user->setChatId(1);
			}
		}
	}

	public function getHistory(User $user)
	{
		if (!isset($this->chatRooms[$user->getChatId()])) {
			return [];
		}

		$room = $this->chatRooms[$user->getChatId()];
		/* @var $room ChatRoom */

		return $room->getHistory($user->getLastMsgId());
	}

	public function addLine(Response $response)
	{
		if (!isset($this->chatRooms[$response->getChatId()])) {
			$this->fetchRoom($response->getChatId());
		}

		$room = $this->chatRooms[$response->getChatId()];
		/* @var $room ChatRoom */

		return $room->pushResponse($response);
	}
}