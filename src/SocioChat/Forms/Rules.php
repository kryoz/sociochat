<?php

namespace SocioChat\Forms;

use SocioChat\Clients\ChannelsCollection;
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

	public static function namePattern($c = 20, $hasSpaces = false)
	{
		return function ($val) use ($c, $hasSpaces) {
			$name = trim($val);
			$pattern = "~^([A-Za-zА-Яа-я0-9_-".($hasSpaces ? '\s' : '')."]+)$~uis";

			if (preg_match($pattern, $name)) {
				return mb_strlen($name) <= $c;
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

	public static function isUserOnline()
	{
		return function ($userId) {
			return UserCollection::get()->getClientById(trim($userId));
		};
	}

	public static function existsChannel()
	{
		return function ($id) {
			$channel = ChannelsCollection::get()->getChannelById(trim($id));
			return $channel && !$channel->isPrivate();
		};
	}

	public static function channelNameDuplication()
	{
		return function ($name) {
			return ChannelsCollection::get()->getChannelByName(trim($name));
		};
	}

}
