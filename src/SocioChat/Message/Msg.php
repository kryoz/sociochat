<?php

namespace SocioChat\Message;


class Msg extends MsgContainer
{
	const MAX_MSG_LENGTH = 1024;

	public function getMsg(Lang $lang = null)
	{
		$text = strip_tags(htmlentities($this->args[0]));

		if (mb_strlen($text) > self::MAX_MSG_LENGTH) {
			$text = mb_strcut($text, 0, self::MAX_MSG_LENGTH) . '...';
		}

		return $text;
	}
}