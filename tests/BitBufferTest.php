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
use chillerlan\QRCode\QRConst;

class BitBufferTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @var \chillerlan\QRCode\BitBuffer
	 */
	protected $bitBuffer;

	protected function setUp(){
		$this->bitBuffer = new BitBuffer;
	}

	public function bitProvider(){
		return [
			[QRConst::MODE_NUMBER,    16],
			[QRConst::MODE_ALPHANUM,  32],
			[QRConst::MODE_BYTE,      64],
			[QRConst::MODE_KANJI,    128],
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
