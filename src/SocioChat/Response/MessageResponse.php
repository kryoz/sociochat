<?php

namespace SocioChat\Response;

use Core\BaseException;
use SocioChat\Message\MsgContainer;

class MessageResponse extends Response
{
	/**
	 * @var MsgContainer|null
	 */
	protected $msgObj;
    /**
     * @var MsgContainer|null
     */
    protected $filteredMsgObj;

	protected $msg;
	protected $time;
	protected $dualChat;
	protected $toName;
	protected $lastMsgId;

	protected $privateProperties = ['privateProperties', 'chatId', 'from', 'recipient', 'msgObj', 'filteredMsgObj'];

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

    public function getFilteredMsg()
    {
        return $this->filteredMsgObj;
    }

    public function setFilteredMsg(MsgContainer $msg)
    {
        $this->filteredMsgObj = $msg;
        return $this;
    }

	public function setTime($time)
	{
		$this->time = $time ? : date('H:i:s');
		return $this;
	}

	public function getTime()
	{
		return $this->time;
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
		if ($this->msgObj) {
			if (!$this->getRecipient()) {
				throw new BaseException('Something weird happened: no user was set as recipient');
			}

            $user = $this->getRecipient();
            if ($user->getProperties()->hasCensor() && $this->filteredMsgObj) {
                $this->msg = $this->filteredMsgObj->getMsg($user->getLang());
            } else {
                $this->msg = $this->msgObj->getMsg($user->getLang());
            }
		}

		return parent::toString();
	}
}
