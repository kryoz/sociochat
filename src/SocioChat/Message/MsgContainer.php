<?php

namespace SocioChat\Message;

abstract class MsgContainer
{
	protected $args;

	public function __construct($args)
	{
		if (!func_get_arg(0)) {
			throw new \Exception('Message has not been set');
		}
		$this->args = $args;
	}

	public static function create()
	{
		return new static(func_get_args());
	}

	abstract public function getMsg(Lang $lang = null);
} 