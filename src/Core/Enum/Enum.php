<?php

namespace Core\Enum;

abstract class Enum
{
	const ANY = 1;
	protected static $names = [];
	protected $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	final static function create($id)
	{
		return new static($id);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return static::$names[$this->id];
	}

	/**
	 * @return Enum[]
	 */
	public static function getList()
	{
		$list = [];
		foreach (array_keys(static::$names) as $id)
			$list[] = static::create($id);

		return $list;
	}

	public static function getNameList()
	{
		return static::$names;
	}
}
