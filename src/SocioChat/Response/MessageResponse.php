<?php

namespace SocioChat\Response;

use SocioChat\Message\MsgContainer;

class MessageResponse extends Response
{
	const MAX_MSG_LENGTH = 1024;

	protected $msg = null;
	protected $time = null;
	protected $dualChat = null;
	protected $toName = null;
	protected $lastMsgId = null;

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
		return $this->msg;
	}

	public function setMsg(MsgContainer $msg)
	{
		$text = $msg->getMsg($this->getFrom()->getLang());
		$text = strip_tags(htmlentities($text));

		if (mb_strlen($text) > self::MAX_MSG_LENGTH) {
			$text = mb_strcut($text, 0, self::MAX_MSG_LENGTH) . '...';
		}

		$this->msg = $text;
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
}
