<?php

namespace SocioChat\Forms;

use SocioChat\Clients\UserCollection;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

class Rules extends \Core\Form\Rules
{
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
