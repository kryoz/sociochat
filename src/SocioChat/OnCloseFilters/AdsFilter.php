<?php

namespace SocioChat\OnCloseFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;

class AdsFilter implements ChainInterface
{
    public function handleRequest(ChainContainer $chain)
    {
        $user = $chain->getFrom();

        $ads = \SocioChat\OnMessageFilters\AdsFilter::get();

        $ads->deleteAdTimer($user);
        $ads->deleteMsgTimer($user);
        $ads->deleteLastMsgId($user);

        return true;
    }
}