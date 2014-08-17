<?php

namespace Core;

trait TSingleton
{
	private static $instance;

	/**
	 * @return $this
	 */
	final static function get()
	{
		if (empty(self::$instance)) {
			self::$instance = new static;
		}

		return static::$instance;
	}
}
