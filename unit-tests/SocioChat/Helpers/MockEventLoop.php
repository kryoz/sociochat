<?php

namespace Test\SocioChat\Helpers;


use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class MockEventLoop implements LoopInterface
{


	public function addReadStream($stream, callable $listener)
	{

	}

	public function addWriteStream($stream, callable $listener)
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

	public function addTimer($interval, callable $callback)
	{
		return new MockTimer($this, 0, function() {});
	}

	public function addPeriodicTimer($interval, callable $callback)
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

	/**
	 * Schedule a callback to be invoked on the next tick of the event loop.
	 *
	 * Callbacks are guaranteed to be executed in the order they are enqueued,
	 * before any timer or stream events.
	 *
	 * @param callable $listener The callback to invoke.
	 */
	public function nextTick(callable $listener)
	{

	}

	/**
	 * Schedule a callback to be invoked on a future tick of the event loop.
	 *
	 * Callbacks are guaranteed to be executed in the order they are enqueued.
	 *
	 * @param callable $listener The callback to invoke.
	 */
	public function futureTick(callable $listener)
	{

	}
}