<?php

namespace SocioChat\Clients;

use SocioChat\Response\MessageResponse;
use SocioChat\Response\Response;

class Channel
{
	const BUFFER_LENGTH = 100;

	private $id;
	/**
	 * @var Response[]
	 */
	protected $history = [];
	protected $lastMsgId = 1;
	protected $name;
	protected $isPrivate = true;
	protected $ownerId = 1;

	public function __construct($id, $name = null, $isPrivate = true)
	{
		$this->id = $id;
		$this->name = $name;
		$this->isPrivate = $isPrivate;
	}

	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

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

	public function setIsPrivate($isPrivate)
	{
		$this->isPrivate = $isPrivate;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isPrivate()
	{
		return $this->isPrivate;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name ?: $this->getId();
	}

	// for the future
	public function setOwnerId($ownerId)
	{
		$this->ownerId = $ownerId;
		return $this;
	}

	// for the future
	public function getOwnerId()
	{
		return $this->ownerId;
	}


	private function filterMessages(MessageResponse $response)
	{
		$response->setGuests(null);

		if (!$response->getMsg()) {
			return false;
		}
	}
}
