<?php
/**
 * Created by PhpStorm.
 * User: kryoz
 * Date: 2/23/14
 * Time: 12:13 PM
 */

namespace Tests;

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