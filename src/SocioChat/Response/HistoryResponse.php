<?php

namespace SocioChat\Response;

use Core\BaseException;
use SocioChat\Clients\Channel;
use SocioChat\Message\MsgContainer;

class HistoryResponse extends Response
{
    protected $history = [];
    protected $clear;
    protected $lastMsgId;

    /**
     * @param mixed $clear
     * @return $this
     */
    public function setClear($clear)
    {
        $this->clear = $clear;
        return $this;
    }

    /**
     * @param int $lastMsgId
     * @return $this
     */
    public function setLastMsgId($lastMsgId)
    {
        $this->lastMsgId = $lastMsgId;
        return $this;
    }

    public function addResponse(array $responsePart)
    {
        $this->history[] = $responsePart;
        return $this;
    }

    public function toString()
    {
        foreach ($this->history as &$responsePart) {
            if (isset($responsePart[Channel::MSG]) && $responsePart[Channel::MSG] instanceof MsgContainer) {
                if (!$this->getRecipient()) {
                    throw new BaseException('Something weird happened: no user was set as recipient');
                }
                $responsePart[Channel::MSG] = $responsePart[Channel::MSG]->getMsg($this->getRecipient()->getLang());
            }
        }

        return parent::toString();
    }
}
