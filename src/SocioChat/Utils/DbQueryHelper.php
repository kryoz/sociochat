<?php

namespace SocioChat\Utils;


class DbQueryHelper 
{
	public static function commaSeparatedHolders(array $list)
	{
		return implode(', ', array_fill(0, count($list), '?'));
	}
} 