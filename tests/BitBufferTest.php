<?php
/**
 * @filesource   BitBufferTest.php
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\BitBuffer;
use chillerlan\QRCode\Data\QRDataInterface;
use PHPUnit\Framework\TestCase;

class BitBufferTest extends TestCase{

	/**
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	protected function setUp(){
		$this->bitBuffer = new BitBuffer;
	}

	public function bitProvider(){
		return [
			[QRDataInterface::MODE_NUMBER,    16],
			[QRDataInterface::MODE_ALPHANUM,  32],
			[QRDataInterface::MODE_BYTE,      64],
			[QRDataInterface::MODE_KANJI,    128],
		];
	}

	/**
	 * @dataProvider bitProvider
	 */
	public function testPut($data, $value){
		$this->bitBuffer->put($data, 4);
		$this->assertEquals($value, $this->bitBuffer->buffer[0]);
		$this->assertEquals(4, $this->bitBuffer->length);
	}

	public function testClear(){
		$this->bitBuffer->clear();
		$this->assertEquals([], $this->bitBuffer->buffer);
		$this->assertEquals(0, $this->bitBuffer->length);
	}

}
