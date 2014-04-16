<?php

namespace Test\SocioChat\Helpers;

use ReflectionClass;

class TestSuite extends \PHPUnit_Framework_TestCase
{
	protected static function getMethod($class, $methodName) {
		$class = new ReflectionClass($class);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);
		return $method;
	}
} 