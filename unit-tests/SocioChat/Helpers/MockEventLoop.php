<?php

namespace Test\SocioChat\Helpers;


use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class MockEventLoop implements LoopInterface
{


	public function addReadStream($stream, $listener)
	{

	}

	public function addWriteStream($stream, $listener)
	{

	}

	public function removeReadStream($stream)
	{

	}

	public function removeWriteStream($stream)
	{

	}

	public function removeStream($stream)
	{

	}

	public function addTimer($interval, $callback)
	{
		return new MockTimer($this, 0, function() {});
	}

	public function addPeriodicTimer($interval, $callback)
	{

	}

	public function cancelTimer(TimerInterface $timer)
	{

	}

	public function isTimerActive(TimerInterface $timer)
	{

	}

	public function tick()
	{

	}

	public function run()
	{

	}

	public function stop()
	{

	}
}