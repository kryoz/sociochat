<?php
/**
 * Created by PhpStorm.
 * User: kryoz
 * Date: 13.04.14
 * Time: 9:29
 */

namespace SocioChat\Message;


class Msg extends MsgContainer
{
	public function getMsg(Lang $lang = null)
	{
		return $this->msg;
	}
}