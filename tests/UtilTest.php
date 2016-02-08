<?php
/**
 * @filesource   UtilTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\Util;

class UtilTest extends PHPUnit_Framework_TestCase{

	public function testIsNumber(){
		$this->assertEquals(true, Util::isNumber('1234567890'));
		$this->assertEquals(false, Util::isNumber('abc'));
	}

	public function testIsAlphaNum(){
		$this->assertEquals(true, Util::isAlphaNum('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'));
		$this->assertEquals(false, Util::isAlphaNum('#'));
	}

	public function testIsKanji(){
		$this->assertEquals(true,  Util::isKanji('茗荷'));
		$this->assertEquals(false, Util::isKanji(''));
		$this->assertEquals(false, Util::isKanji('ÃÃÃ')); // non-kanji
		$this->assertEquals(false, Util::isKanji('荷')); // kanji forced into byte mode due to length
	}

}
