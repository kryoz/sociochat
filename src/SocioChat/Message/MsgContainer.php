<?php

namespace SocioChat\Message;

abstract class MsgContainer
{
	protected  $msg;

	public function __construct($msg)
	{
		$this->msg = $msg;
	}

	abstract public function getMsg(Lang $lang = null);
} 