<?php

namespace SocioChat\Response;

class HistoryResponse extends Response
{
	protected $history = [];

	public function addResponse(Response $response)
	{
		$this->history[] = json_decode($response->toString(), 1);
		return $this;
	}
}
