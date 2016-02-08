<?php
/**
 * @filesource   UtilTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\Util;
use chillerlan\QRCode\QRConst;

class UtilTest extends PHPUnit_Framework_TestCase{

	public function testIsNumber(){
		$this->assertEquals(true, Util::isNumber('1234567890'));
	}

	public function testIsAlphaNum(){
		$this->assertEquals(true, Util::isAlphaNum('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'));
	}

	public function testIsKanji(){
		$this->assertEquals(true, Util::isKanji('茗荷'));
	}

}
