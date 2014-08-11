<?php
namespace SocioChat\Utils;

class CMDUtils
{

	/**
	 * Возвращает ассоциативный массив стартовых настроек для скрипта
	 * Настройки должны быть вида --param1=value1
	 * @return array
	 */
	public static function getOptionsList()
	{
		$uArguments = $_SERVER['argv'];
		array_shift($uArguments);
		$arguments = array();
		foreach ($uArguments as $argument) {
			if ($position = mb_strpos($argument, '=')) {
				$arguments[mb_substr($argument, 0, $position)] = mb_substr($argument, $position + 1);
			} else {
				$arguments[$argument] = true;
			}
		}

		return $arguments;
	}
}