<?php

namespace SocioChat\Forms;

use Core\Form\Form;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\User;
use SocioChat\DI;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use SocioChat\Utils\RudeFilter;

class Rules extends \Core\Form\Rules
{
	const LOWEST_YEAR = 1930;

    public static function namePattern($c = 20, $hasSpaces = false)
    {
        return function ($val) use ($c, $hasSpaces) {
            $name = trim($val);
            $name = RudeFilter::parse($name);
            $pattern = "~^([A-Za-zА-Яа-я0-9_-".($hasSpaces ? '\s' : '')."]+)$~uis";

            if (preg_match($pattern, $name)) {
                return mb_strlen($name) <= $c;
            }
        };
    }

    public static function cityPattern()
    {
        return function ($val) {
            $name = trim($val);
            if (!$name) {
                return true;
            }
            $name = RudeFilter::parse($name);
            $pattern = "~^([A-Za-zА-Яа-я- ]+)$~uis";

            if (preg_match($pattern, $name)) {
                return mb_strlen($name) <= 50;
            }
        };
    }

    public static function birthYears()
    {
        return function ($val) {
            $val = trim($val);
            return in_array($val, self::getBirthYearsRange());
        };
    }

    public static function getBirthYearsRange()
    {
        return range(self::LOWEST_YEAR, date('Y') - 8);
    }

    public static function timPattern()
    {
        return function ($tim) {
            $val = (int)$tim;
            return $val >= TimEnum::FIRST && $val <= TimEnum::LAST;
        };
    }

    public static function sexPattern()
    {
        return function ($sex) {
            $val = (int)$sex;
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
