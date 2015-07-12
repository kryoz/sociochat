<?php

namespace SocioChat\Enum;

use Core\Enum\Enum;

class MsgAnimationEnum extends Enum
{
    const SIMPLE = 1;
    const LEFT = 2;
    const FADE = 3;

    const FIRST = self::SIMPLE;
    const LAST = self::FADE;

    protected static $names = [
        self::SIMPLE => 'Без анимации',
        self::LEFT => 'Выплывание слева',
        self::FADE => 'Фейдинг',
    ];
} 