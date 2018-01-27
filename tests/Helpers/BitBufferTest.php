<?php
/**
 * Class BitBufferTest
 *
 * @filesource   BitBufferTest.php
 * @created      08.02.2016
 * @package      chillerlan\QRCodeTest\Helpers
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Helpers;

use chillerlan\QRCode\Helpers\BitBuffer;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCodeTest\QRTestAbstract;

class BitBufferTest extends QRTestAbstract{

	/**
	 * @var \chillerlan\QRCode\Helpers\BitBuffer
	 */
	protected $bitBuffer;

	protected function setUp(){
		$this->bitBuffer = new BitBuffer;
	}

	public function bitProvider(){
		return [
			[QRCode::DATA_NUMBER, 16],
			[QRCode::DATA_ALPHANUM, 32],
			[QRCode::DATA_BYTE, 64],
			[QRCode::DATA_KANJI, 128],
		];
	}

	/**
	 * @dataProvider bitProvider
	 */
	public function testPut($data, $value){
		$this->bitBuffer->put($data, 4);
		$this->assertSame($value, $this->bitBuffer->buffer[0]);
		$this->assertSame(4, $this->bitBuffer->length);
	}

	public function testClear(){
		$this->bitBuffer->clear();
		$this->assertSame([], $this->bitBuffer->buffer);
		$this->assertSame(0, $this->bitBuffer->length);
	}

}
