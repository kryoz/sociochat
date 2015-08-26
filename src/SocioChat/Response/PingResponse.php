<?php

namespace SocioChat\Response;

class PingResponse extends Response
{
    public function toString()
    {
        return json_encode(
            [
                'ping' => 'pong',
            ]
        );
    }
}