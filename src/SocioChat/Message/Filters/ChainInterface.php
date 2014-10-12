<?php

namespace SocioChat\Message\Filters;

interface ChainInterface
{
    /**
     * C-o-R pattern
     * @param Chain $chain input stream
     * @return false|null|true
     */
    public function handleRequest(Chain $chain);
}
