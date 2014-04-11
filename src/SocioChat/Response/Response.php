<?php
namespace SocioChat\Response;

use SocioChat\Clients\User;

abstract class Response
{
	protected $guests;
	protected $fromName;
	/**
	 * @var User
	 */
	protected $from;
	protected $chatId;
	protected $privateProperties = ['privateProperties', 'chatId', 'from'];

	public function setChatId($chatId)
	{
		$this->chatId = $chatId;
		return $this;
	}

	public function getChatId()
	{
		return $this->chatId;
	}

	/**
	 * @param array $guests
	 * @return $this
	 */
	public function setGuests(array $guests = null)
	{
		if ($guests === null) {
			$this->guests = null;
			return $this;
		}

		foreach ($guests as $user) {
			/* @var $user User */
			$this->guests[] = $user->getProperties()->toPublicArray();
		}

		return $this;
	}

	public function setGuestsRaw($guests)
	{
		$this->guests = $guests;
		return $this;
	}

	/**
	 * @return array|null
	 */
	public function getGuests()
	{
		return $this->guests;
	}

	public function getFromName()
	{
		return $this->from->getProperties()->getName();
	}

	public function setFrom(User $user)
	{
		$this->from = $user;
		$this->fromName = $this->getFromName();

		return $this;
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function toString()
	{
		$arr = [];

		$reflection = new \ReflectionClass(new static);

		foreach ($reflection->getProperties() as $property) {
			$pName = $property->getName();
			$val = $this->{$pName};

			if ($val === null) {
				continue;
			}
			if (!in_array($pName, $this->privateProperties)) {
				$arr += [ $pName => $this->{$pName} ];
			}
		}

		return json_encode($arr);
	}
}
