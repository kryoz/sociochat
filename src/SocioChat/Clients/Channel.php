<?php

namespace SocioChat\Clients;

use Core\DI;
use Core\Form\Form;
use SocioChat\Response\MessageResponse;

class Channel
{
	const BUFFER_LENGTH = 100;

	private $id;
	const TO_NAME = 'toName';
	const FROM_NAME = 'fromName';
	const TIM = 'tim';
	const SEX = 'sex';
	const AVATAR_THUMB = 'avatarThumb';
	const AVATAR_IMG = 'avatarImg';
	const TIME = 'time';
	const MSG = 'msg';
	const USER_INFO = 'userInfo';
	const FROM_USER_ID = 'fromUserId';

	protected $history = [];
	protected $lastMsgId = 1;

	private $onJoinRule;

	/**
	 * @return int
	 */
	public function getLastMsgId()
	{
		return $this->lastMsgId;
	}
	protected $name;
	protected $isPrivate = true;
	protected $ownerId = 1;

	public function __construct($id, $name = null, $isPrivate = true)
	{
		$this->id = $id;
		$this->name = $name;
		$this->isPrivate = $isPrivate;
		$this->onJoinRule = function (Form $form, User $user) {
			return true;
		};
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

		$this->history[$this->lastMsgId] = $this->makeRecord($response);
		$keys = array_keys($this->history);

		if (count($this->history) > self::BUFFER_LENGTH) {
			unset($this->history[$keys[0]]);
		}

		$id = $this->lastMsgId;

		if ($id < self::BUFFER_LENGTH) {
			$this->lastMsgId++;
		}

		return $id;
	}

	/**
	 * @param int $lastMsgId
	 * @return MessageResponse[]
	 */
	public function getHistory($lastMsgId)
	{
		$history = $this->history;

		if ($lastMsgId > 0 && $lastMsgId <= count($history)) {
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

	public function setOnJoinRule(callable $rule)
	{
		$this->onJoinRule = $rule;
		return $this;
	}

	public function verifyOnJoinRule()
	{
		return $this->onJoinRule;
	}

	private function filterMessages(MessageResponse $response)
	{
		$response->setGuests(null);

		if (!$response->getMsg()) {
			return false;
		}
	}

	/**
	 * @param MessageResponse $response
	 * @return array
	 */
	private function makeRecord(MessageResponse $response)
	{
		$record = [
			self::FROM_USER_ID => null,
			self::FROM_NAME    => $response->getFromName(),
			self::TIME         => $response->getTime(),
			self::MSG          => $response->getMsg(),
		];

		if ($from = $response->getFrom()) {
			$dir = DI::get()->getConfig()->uploads->avatars->wwwfolder . DIRECTORY_SEPARATOR;
			$info = [
				self::TIM => $from->getProperties()->getTim()->getName(), //@TODO wrong lang
				self::SEX => $from->getProperties()->getSex()->getName(),
			];


			if ($from->getProperties()->getAvatarThumb()) {
				$info += [
					self::AVATAR_THUMB => $dir . $from->getProperties()->getAvatarThumb(),
					self::AVATAR_IMG   => $dir . $from->getProperties()->getAvatarImg(),
				];
			}

			$record += [
				self::USER_INFO => $info
			];
			$record[self::FROM_USER_ID] = $response->getFrom()->getId();
		}

		if ($response->getToUserName()) {
			$record += [self::TO_NAME => $response->getToUserName()];
			return $record;
		}
		return $record;
	}
}
