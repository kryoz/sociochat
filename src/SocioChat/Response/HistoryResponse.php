<?php

namespace SocioChat\Response;

class HistoryResponse extends Response
{
	protected $history = [];
	protected $clear;

	/**
	 * @param mixed $clear
	 * @return $this
	 */
	public function setClear($clear)
	{
		$this->clear = $clear;
		return $this;
	}


	public function addResponse(Response $response)
	{
		$this->history[] = json_decode($response->toString(), 1);
		return $this;
	}
}
