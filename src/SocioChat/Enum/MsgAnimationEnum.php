<?php

namespace SocioChat\Enum;

use Core\Enum\Enum;

class MsgAnimationEnum extends Enum
{
    const SIMPLE = 1;
    const LEFT = 2;

    const FIRST = self::SIMPLE;
    const LAST = self::LEFT;

    protected static $names = [
        self::SIMPLE => 'Без анимации',
        self::LEFT => 'Выплывание слева',
    ];
} 