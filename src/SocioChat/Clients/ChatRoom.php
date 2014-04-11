<?php

namespace SocioChat\Clients;

use SocioChat\Response\MessageResponse;
use SocioChat\Response\Response;

class ChatRoom
{
	const BUFFER_LENGTH = 100;

	/**
	 * @var Response[]
	 */
	protected $history = [];
	protected $lastMsgId = 1;

	/**
	 * @param MessageResponse $response
	 * @return int
	 */
	public function pushResponse(MessageResponse $response)
	{
		if ($this->filterMessages($response) === false) {
			return;
		}

		$this->history[$this->lastMsgId] = $response;
		$keys = array_keys($this->history);

		if (count($this->history) > self::BUFFER_LENGTH) {
			unset($this->history[$keys[0]]);
		}

		$id = $this->lastMsgId;
		$this->lastMsgId++;

		return $id;
	}

	/**
	 * @param int $lastMsgId
	 * @return MessageResponse[]
	 */
	public function getHistory($lastMsgId)
	{
		$history = $this->history;

		if ($lastMsgId > 0 && $lastMsgId < count($history)) {
			$history = array_slice($this->history, $lastMsgId, null, true);
		}

		return $history;
	}

	private function filterMessages(MessageResponse $response)
	{
		$response->setGuests(null);

		if (!$response->getMsg()) {
			return false;
		}
	}
}
