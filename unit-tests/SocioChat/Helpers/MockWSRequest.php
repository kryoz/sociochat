<?php

namespace Test\SocioChat\Helpers;


class MockWSRequest
{
	protected $sessionId = 'asd123';

	public function getCookie($str = null)
	{
		return $this->sessionId;
	}

	public function setCookie($id)
	{
		$this->sessionId = $id;
	}
}