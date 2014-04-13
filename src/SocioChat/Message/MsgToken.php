<?php

namespace SocioChat\Message;

class MsgToken extends MsgContainer
{
	public function getMsg(Lang $lang = null)
	{
		if (!$lang) {
			return $this->msg;
		}

		return $lang->getPhrase($this->msg);
	}
}