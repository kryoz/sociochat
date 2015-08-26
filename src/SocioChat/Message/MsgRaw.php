<?php

namespace SocioChat\Message;


class MsgRaw extends MsgContainer
{
    public function getMsg(Lang $lang = null)
    {
        return $this->args[0];
    }
}