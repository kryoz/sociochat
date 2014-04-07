<?php
/**
 * Created by PhpStorm.
 * User: kryoz
 * Date: 16.03.14
 * Time: 15:48
 */

namespace MyApp;

use React\EventLoop\Factory as Loop;
use MyApp\TSingleton;

class MightyLoop
{
	use TSingleton;

	protected $loop;

	public function __construct()
	{
		$this->loop = Loop::create();
	}

	public function fetch()
	{
		return $this->loop;
	}
} 