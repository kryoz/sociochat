<?php

namespace SocioChat\Message;

class MsgToken extends MsgContainer
{
	public function getMsg(Lang $lang = null)
	{
		if (!$lang) {
			return $this->args[0];
		}

		return $lang->getPhraseByArray($this->args);
	}
}