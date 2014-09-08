<?php

namespace Core\Form;

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
		return function ($val, Form $form) use ($c, $hasSpaces) {
			$name = trim($val);
			$pattern = "~^([A-Za-zА-Яа-я0-9_-".($hasSpaces ? '\s' : '')."]+)$~uis";

			$isCorrect = preg_match($pattern, $name) && mb_strlen($name) <= $c;
			if (!$isCorrect) {
				$form->markWrong($val, 'Некорректный формат email');
			}
			return $isCorrect;
		};
	}

	public static function email()
	{
		return function ($val, Form $form) {
			$isCorrect = preg_match("~^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$~uis", trim($val));
			if (!$isCorrect) {
				$form->markWrong($val, 'Некорректный формат email');
			}
			return $isCorrect;
		};
	}

	public static function password()
	{
		return function ($val, Form $form) {
			$len = mb_strlen(trim($val));
			$isCorrect =  $len >= 8 && $len <= 20;

			if (!$isCorrect) {
				$form->markWrong($val, 'Пароль должен быть от 8 до 20 символов');
			}
			return $isCorrect;
		};
	}

	public static function colorPattern()
	{
		return function ($val) {
			return preg_match("~^\#[0-9A-Z]{6}$~uis", trim($val));
		};
	}
}
