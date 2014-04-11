<?php

namespace SocioChat\Forms;

use SocioChat\Clients\UserCollection;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

class Rules
{
	public static function notNull()
	{
		return function ($val) {
			return $val != '';
		};
	}

	public static function boolean()
	{
		return function ($val) {
			return $val === false || $val === true;
		};
	}

	public static function namePattern()
	{
		return function ($val) {
			$name = trim($val);
			if (preg_match("~^([\d\w]+)$~uis", $name)) {
				return mb_strlen($name) <= 20;
			}
		};
	}

	public static function email()
	{
		return function ($val) {
			return preg_match("~^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$~uis", trim($val));
		};
	}

	public static function password()
	{
		return function ($val) {
			$len = mb_strlen(trim($val));
			return $len >= 8 && $len <= 20;
		};
	}

	public static function colorPattern()
	{
		return function ($val) {
			return preg_match("~^\#[0-9A-Z]{6}$~uis", trim($val));
		};
	}

	public static function timPattern()
	{
		return function ($tim) {
			$val = (int) $tim;
			return $val >= TimEnum::FIRST && $val <= TimEnum::LAST;
		};
	}

	public static function sexPattern()
	{
		return function ($sex) {
			$val = (int) $sex;
			return $val >= SexEnum::FIRST && $val <= SexEnum::LAST;
		};
	}

	public static function UserOnline()
	{
		return function ($userId) {
			return UserCollection::get()->getClientById($userId);
		};
	}
}
