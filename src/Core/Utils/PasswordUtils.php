<?php
namespace Core\Utils;

class PasswordUtils
{
	static private $charLists = [
		'qwertyuiopasdfghjklzxcvbnm',
		'QWERTYUIOPASDFGHJKLZXCVBNM',
		'0123456789',
		//'!?<>{}[]@#$%^&*();:',
	];

	public static function get($size = 8)
	{
		if ($size < 1 || $size > 100) {
			throw new WrongArgumentException('expects size between 1 and 100');
		}

		$passwords = [];
		for ($k = 0; $k < 10; $k++) {
			$words = [];
			for ($i = 0; $i < $size; $i++) {
				$words[] = self::getWord($i);
			}

			shuffle($words);
			$password = implode('', $words);
			$passwords[] = [$password, self::checkStrength($password, $size, $size)];
		}

		usort(
			$passwords,
			function ($left, $right) {
				return $left[1] != $right[1]
					? ($left[1] < $right[1] ? 1 : -1)
					: 0;
			}
		);
		return $passwords[0][0];
	}

	public static function checkStrength($password, $minLength = 6, $maxLength = 12)
	{
		if (($length = mb_strlen($password)) < $minLength) {
			return 0;
		}

		$types = [];
		$typeStr = '';
		for ($i = 0; $i < $length; $i++) {
			$type = self::getWordType(mb_substr($password, $i, 1));
			if ($type === null) {
				return null;
			}

			$typeStr .= $type;
			$types[$type] = (isset($types[$type]) ? $types[$type] : 0) + 1;
		}

		$baseMultiplier = (float)(count(array_keys($types)) / count(self::$charLists));
		$typeMultiplier = 1;
		foreach ($types as $type => $count) {
			$offset = 0;
			$typeAmount = 0;
			for ($i = 0; $i < $count; $i++) {
				$pos = mb_strpos($typeStr, $type, $offset);
				$offset = $pos + 1;
				if ($pos == 0 || mb_substr($typeStr, $pos - 1, 1) != $type) {
					$left = 1;
				} else {
					$left = mb_substr($password, $pos - 1, 1) == mb_substr($password, $pos, 1) ? 0.5 : 0.75;
				}
				if ($pos == $length - 1 || mb_substr($typeStr, $pos + 1, 1) != $type) {
					$right = 1;
				} else {
					$right = mb_substr($password, $pos + 1, 1) == mb_substr($password, $pos, 1) ? 0.5 : 0.75;
				}

				$typeAmount += $left + $right;
			}
			$typeMultiplier *= floatval($typeAmount / $count / 2);
		}

		if ($length < $minLength) {
			$lengthMultiplier = 0;
		} elseif ($length >= $maxLength) {
			$lengthMultiplier = 1;
		} else {
			$lengthMultiplier = ($length - $minLength) / ($maxLength - $minLength);
		}

		return $baseMultiplier * $typeMultiplier * $lengthMultiplier;
	}

	private static function getWord($i)
	{
		$charList = isset(self::$charLists[$i])
			? self::$charLists[$i]
			: self::$charLists[array_rand(self::$charLists)];

		$size = mb_strlen($charList);
		return $charList[rand(0, $size - 1)];
	}

	private static function getWordType($char)
	{
		foreach (self::$charLists as $type => $charList) {
			if (mb_strpos($charList, $char) !== false) {
				return $type;
			}
		}
		return null;
	}
}
