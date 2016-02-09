<?php
/**
 * @filesource   UtilTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\Util;

class UtilTest extends \PHPUnit_Framework_TestCase{

	public function testIsNumber(){
		$this->assertEquals(true, Util::isNumber('1234567890'));
		$this->assertEquals(false, Util::isNumber('abc'));
	}

	public function testIsAlphaNum(){
		$this->assertEquals(true, Util::isAlphaNum('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'));
		$this->assertEquals(false, Util::isAlphaNum('#'));
	}

	// http://stackoverflow.com/a/24755772
	public function testIsKanji(){
		$this->assertEquals(true,  Util::isKanji('茗荷'));
		$this->assertEquals(false, Util::isKanji(''));
		$this->assertEquals(false, Util::isKanji('ÃÃÃ')); // non-kanji
		$this->assertEquals(false, Util::isKanji('荷')); // kanji forced into byte mode due to length
	}

	// coverage
	public function testGetBCHTypeNumber(){
		$this->assertEquals(7973, Util::getBCHTypeNumber(1));
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage $typeNumber: 1 / $errorCorrectLevel: 42
	 */
	public function testGetRSBlocksException(){
		Util::getRSBlocks(1, 42);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid error correct level: 42
	 */
	public static function testGetMaxLengthECLevelException(){
		Util::getMaxLength(QRCode::TYPE_01, QRConst::MODE_BYTE, 42);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid mode: 1337
	 */
	public static function testGetMaxLengthModeException(){
		Util::getMaxLength(QRCode::TYPE_01, 1337, QRCode::ERROR_CORRECT_LEVEL_H);
	}

}
