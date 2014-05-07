<?php

namespace SocioChat\Response;

use SocioChat\Message\MsgContainer;

class MessageResponse extends Response
{
	/**
	 * @var MsgContainer|null
	 */
	protected $msgObj;

	protected $msg = null;
	protected $time = null;
	protected $dualChat = null;
	protected $toName = null;
	protected $lastMsgId = null;

	protected $privateProperties = ['privateProperties', 'chatId', 'from', 'recipient', 'msgObj'];

	/**
	 * @param string $userName
	 * @return $this
	 */
	public function setToUserName($userName)
	{
		$this->toName = $userName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getToUserName()
	{
		return $this->toName;
	}

	public function getMsg()
	{
		return $this->msgObj;
	}

	public function setMsg(MsgContainer $msg)
	{
		$this->msgObj = $msg;
		return $this;
	}

	public function setTime($time)
	{
		$this->time = $time ? : date('H:i:s');
		return $this;
	}

	public function setDualChat($dualChat)
	{
		$this->dualChat = $dualChat;
		return $this;
	}

	/**
	 * @param null $lastMsgId
	 */
	public function setLastMsgId($lastMsgId)
	{
		$this->lastMsgId = $lastMsgId;
	}

	public function toString()
	{
		if ($text = $this->msgObj) {
			$this->msg = $this->msgObj->getMsg($this->getRecipient()->getLang());
		}

		return parent::toString();
	}
}
