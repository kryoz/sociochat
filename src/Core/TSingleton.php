<?php

namespace Core;

trait TSingleton
{
	protected static $instance;

	/**
	 * @return $this
	 */
	final static function get()
	{
		if (empty(static::$instance)) {
			static::$instance = new static;
		}

		return static::$instance;
	}
}
