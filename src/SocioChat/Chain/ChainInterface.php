<?php

namespace SocioChat\Chain;

interface ChainInterface
{
	public function handleRequest(ChainContainer $chain);
}