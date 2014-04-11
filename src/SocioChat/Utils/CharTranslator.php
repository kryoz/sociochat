<?php

namespace SocioChat\Utils;


class CharTranslator 
{
	protected static $charMap = [
		'а' => 'a',
		'е' => 'e',
		'с' => 'c',
		'у' => 'y',
		'р' => 'p',
		'о' => 'o',
		'х' => 'x',
		'А' => 'A',
		'Е' => 'E',
		'С' => 'C',
		'Р' => 'P',
		'О' => 'O',
		'Х' => 'X',
		'Т' => 'T',
		'М' => 'M',
		'К' => 'K',
		'Н' => 'H',
		'З' => '3'
	];

	public static function toRussian($sample)
	{
		foreach (array_flip(self::$charMap) as $eng => $rus) {
			$sample = str_replace($eng, $rus, $sample);
		}

		return $sample;
	}

	public static function toEnglish($sample)
	{
		foreach (self::$charMap as $rus => $eng) {
			$sample = str_replace($rus, $eng, $sample);
		}

		return $sample;
	}
} 