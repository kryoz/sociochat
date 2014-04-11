<?php

namespace SocioChat\Enum;

class TimEnum extends Enum
{
	const ANY = 1;

	const ILE = 2;
	const SEI = 3;
	const LII = 4;
	const ESE = 5;

	const IEI = 6;
	const SLE = 7;
	const EIE = 8;
	const LSI = 9;

	const ILI = 10;
	const SEE = 11;
	const LIE = 12;
	const ESI = 13;

	const IEE = 14;
	const SLI = 15;
	const EII = 16;
	const LSE = 17;



	const FIRST = self::ANY;
	const LAST = self::LSE;

	protected static $names = [
		self::ANY => 'Аноним',

		self::ILE => 'Дон Кихот (ИЛЭ)',
		self::SEI => 'Дюма (СЭИ)',
		self::LII => 'Робеспьер (ЛИИ)',
		self::ESE => 'Гюго (ЭСЭ)',

		self::IEI => 'Есенин (ИЭИ)',
		self::SLE => 'Жуков (СЛЭ)',
		self::EIE => 'Гамлет (ЭИЭ)',
		self::LSI => 'Максим (ЛСИ)',

		self::ILI => 'Бальзак (ИЛИ)',
		self::SEE => 'Наполеон (СЭЭ)',
		self::LIE => 'Джек Лондон (ЛИЭ)',
		self::ESI => 'Драйзер (ЭСИ)',

		self::IEE => 'Гексли (ИЭЭ)',
		self::SLI => 'Габен (СЛИ)',
		self::EII => 'Достоевский (ЭИИ)',
		self::LSE => 'Штирлиц (ЛСЭ)',

	];

	protected static $shortNames = [
		self::ANY => 'Аноним',

		self::ILE => 'Дон',
		self::SEI => 'Дюма',
		self::LII => 'Робик',
		self::ESE => 'Гюго',

		self::IEI => 'Есенин',
		self::SLE => 'Жуков',
		self::EIE => 'Гамлет',
		self::LSI => 'Максим',

		self::ILI => 'Бальзак',
		self::SEE => 'Наполеон',
		self::LIE => 'Джек',
		self::ESI => 'Драйзер',

		self::IEE => 'Гексли',
		self::SLI => 'Габен',
		self::EII => 'Достик',
		self::LSE => 'Штирлиц',
	];

	public function getShortName()
	{
		return static::$shortNames[$this->id];
	}
} 