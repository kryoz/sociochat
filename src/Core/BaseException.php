<?php

namespace Core;

class BaseException extends \Exception
{
    public function __toString()
    {
        return
            "[$this->message] in: \n" .
            $this->getTraceAsString();
    }
}
