<?php

namespace MyApp\Enum;

class SexEnum extends Enum
{
	const MALE = 1;
	const FEMALE = 2;
	const ANONYM = 3;

	const FIRST = self::MALE;
	const LAST = self::ANONYM;

	protected static $names = [
		self::MALE => 'Мужчина',
		self::FEMALE => 'Женщина',
		self::ANONYM => 'Аноним',
	];
} 