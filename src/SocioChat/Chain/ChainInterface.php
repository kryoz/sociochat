<?php

namespace SocioChat\Chain;

interface ChainInterface
{
    /**
     * C-o-R pattern
     * @param ChainContainer $chain input stream
     * @return false|null|true
     */
    public function handleRequest(ChainContainer $chain);
}