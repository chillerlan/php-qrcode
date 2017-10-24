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
use chillerlan\QRCode\Data\AlphaNum;
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Data\Kanji;
use chillerlan\QRCode\Data\Number;
use chillerlan\QRCode\Data\QRDataInterface;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase{

	/**
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	/**
	 * @var \chillerlan\QRCode\Data\QRDataInterface
	 */
	protected $module;

	public function bitProviderMode(){
		return [
			[QRDataInterface::MODE_NUMBER, Number::class, '123456789'],
			[QRDataInterface::MODE_NUMBER, Number::class, '1234567890'],
			[QRDataInterface::MODE_NUMBER, Number::class, '12345678901'],
			[QRDataInterface::MODE_ALPHANUM, AlphaNum::class, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			[QRDataInterface::MODE_BYTE, Byte::class, '#\\'],
			[QRDataInterface::MODE_KANJI, Kanji::class, '茗荷'],
		];
	}

	protected function setUp(){
		$this->bitBuffer = new BitBuffer;
	}

	/**
	 * @dataProvider bitProviderMode
	 */
	public function testMode($mode, $class, $data){
		$this->module = new $class($data);
		$this->assertEquals($mode, $this->module->mode);
	}


	public function bitProviderWrite(){
		return [
			[Number::class, '123456789', [30, 220, 140, 84]],
			[Number::class, '1234567890', [30, 220, 140, 84, 0]],
			[Number::class, '12345678901', [30, 220, 140, 84, 8]],
			[AlphaNum::class, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', [57, 168, 165, 66, 174, 22, 122, 230, 95, 172, 81, 149, 180, 38, 178, 220, 28, 58, 11, 196, 88, 231, 40, 102, 87, 60, 237, 94, 99, 227, 108]],
			[Byte::class, '#\\', [35, 92]],
			[Kanji::class, '茗荷', [236, 100, 74, 18, 238]],
		];
	}

	/**
	 * @dataProvider bitProviderWrite
	 */
	public function testWrite($class, $data, $expected){
		$this->bitBuffer->clear();
		$this->module = new $class($data);
		$this->module->write($this->bitBuffer);

		$this->assertSame($expected, $this->bitBuffer->buffer);
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
