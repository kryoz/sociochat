<?php

namespace SocioChat\Forms;

use Core\Form\Form;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\User;
use SocioChat\DI;
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

	public static function isUserOnline()
	{
		return function ($userId) {
			return DI::get()->getUsers()->getClientById(trim($userId));
		};
	}

	public static function existsChannel()
	{
		return function ($id) {
			$channel = ChannelsCollection::get()->getChannelById(trim($id));
			return $channel && !$channel->isPrivate();
		};
	}

	public static function verifyOnJoinRule(User $user)
	{
		return function ($id, Form $form) use ($user) {
			$channel = ChannelsCollection::get()->getChannelById(trim($id));
			$rule = $channel->verifyOnJoinRule();
			return $rule($form, $user);
		};
	}

	public static function channelNameDuplication()
	{
		return function ($name) {
			return ChannelsCollection::get()->getChannelByName(trim($name));
		};
	}

}
