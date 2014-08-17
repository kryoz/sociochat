<?php

namespace Core\Utils;


class DbQueryHelper 
{
	public static function commaSeparatedHolders(array $list, $startingIndex = 0)
	{
		$stringParts = [];

		foreach ($list as $i => $param) {
			$stringParts[] = ':'.($i+$startingIndex);
		}

		return implode(', ', $stringParts);
	}

	public static function timestamp2date($timestamp)
	{
		return date('Y-m-d H:i:s', $timestamp);
	}
} 