<?php

namespace SocioChat\Enum;

use Core\Enum\Enum;

class UserRoleEnum extends Enum
{
    const BANNED = 0;
    const USER = 1;
    const MODERATOR = 2;
    const CREATOR = 3;

    const ADMIN = 99;

    const FIRST = self::BANNED;
    const LAST = self::ADMIN;

    protected static $names = [
        self::USER => 'Пользователь',
        self::BANNED => 'Забаненный',
        self::MODERATOR => 'Модератор',
        self::CREATOR => 'Владелец канала',
        self::ADMIN => 'Администратор',
    ];

    public function isAdmin()
    {
        return $this->getId() === self::ADMIN;
    }

    public function isCreator()
    {
        return $this->getId() === self::CREATOR;
    }
}
