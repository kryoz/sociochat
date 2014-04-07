<?php

namespace MyApp\Chain;

interface ChainInterface
{
	public function handleRequest(ChainContainer $chain);
}