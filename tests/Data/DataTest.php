<?php
/**
 *
 * @filesource   DataTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\BitBuffer;
use chillerlan\QRCode\QRConst;
use chillerlan\QRCode\Data\AlphaNum;
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Data\Kanji;
use chillerlan\QRCode\Data\Number;

class DataTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $module;

	public function bitProvider(){
		return [
			[QRConst::MODE_NUMBER, Number::class, '123456789'],
			[QRConst::MODE_NUMBER, Number::class, '1234567890'],
			[QRConst::MODE_NUMBER, Number::class, '12345678901'],
			[QRConst::MODE_ALPHANUM, AlphaNum::class, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			[QRConst::MODE_BYTE, Byte::class, '#\\'],
			[QRConst::MODE_KANJI, Kanji::class, '茗荷'],
		];
	}

	protected function setUp(){
		$this->bitBuffer = new BitBuffer;
	}

	/**
	 * @dataProvider bitProvider
	 */
	public function testMode($mode, $class, $data){
		$this->module = new $class($data);
		$this->assertEquals($mode, $this->module->mode);
	}

	/**
	 * @dataProvider bitProvider
	 */
	public function testWrite($mode, $class, $data){
		$this->bitBuffer->clear();
		$this->module = new $class($data);
		$this->module->write($this->bitBuffer);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char: 92
	 */
	public function testAlphaNumCharException(){
		$this->bitBuffer->clear();
		$this->module = new AlphaNum('\\');
		$this->module->write($this->bitBuffer);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char at 7 (50051)
	 */
	public function testKanjiCharExceptionA(){
		$this->bitBuffer->clear();
		$this->module = new Kanji('茗荷Ã');
		$this->module->write($this->bitBuffer);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char at 7
	 */
	public function testKanjiCharExceptionB(){
		$this->bitBuffer->clear();
		$this->module = new Kanji('茗荷\\');
		$this->module->write($this->bitBuffer);
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char: 92
	 */
	public function testNumberCharException(){
		$this->bitBuffer->clear();
		$this->module = new Number('\\');
		$this->module->write($this->bitBuffer);
	}


}
