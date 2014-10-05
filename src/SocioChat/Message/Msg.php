<?php

namespace SocioChat\Message;


class Msg extends MsgContainer
{
    const MAX_MSG_LENGTH = 1024;

    public function getMsg(Lang $lang = null)
    {
        return $this->args[0];
    }
}