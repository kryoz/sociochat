<?php

namespace Test\SocioChat\Utils;

use SocioChat\Utils\CharTranslator;

class CharTranslatorTest extends \PHPUnit_Framework_TestCase
{

	public function testToRussianConversion()
	{
		$biLingualChars = 'Гocть';
		$russian = 'Гость';

		$this->assertNotEquals($biLingualChars, $russian, 'source words should look similar but not really equal');
		$this->assertEquals($russian, CharTranslator::toRussian($biLingualChars), 'bilingual to russian chars conversion failed');
	}

	public function testToEnglishConversion()
	{
		$biLingualChars = 'Аеrох';
		$english = 'Aerox';

		$this->assertNotEquals($biLingualChars, $english, 'source words should look similar but not really equal');
		$this->assertEquals($english, CharTranslator::toEnglish($biLingualChars), 'bilingual to english chars conversion failed');
	}
}
 