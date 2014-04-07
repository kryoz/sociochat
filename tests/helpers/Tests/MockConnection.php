<?php

namespace Tests;

use Ratchet\ConnectionInterface;
use stdClass;

class MockConnection implements ConnectionInterface
{
	public $resourceId;
	public $WebSocket;

	public $dump = array(
		'send'  => [],
		'close' => []
	);

	public $remoteAddress = '127.0.0.1';

	public function __construct($id = 1)
	{
		$this->resourceId = $id;
		$this->WebSocket = new StdClass();
		$this->WebSocket->request = new MockWSRequest();
	}

	public function send($data) {
		$this->dump[__FUNCTION__][] = $data;
	}

	public function close() {
		$this->dump[__FUNCTION__][] = 1;
	}

	public function setCookie($id)
	{
		$this->WebSocket->request->setCookie($id);
	}
} 