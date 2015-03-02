<?php

namespace SocioChat\Message;

class MsgToken extends MsgContainer
{
    public function getMsg(Lang $lang = null)
    {
        if (!$lang) {
            return implode('|', $this->args);
        }

        return $lang->getPhraseByArray($this->args);
    }
}