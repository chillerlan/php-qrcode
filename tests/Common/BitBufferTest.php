<?php
/**
 * Class BitBufferTest
 *
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\Common\{BitBuffer, Mode};
use PHPUnit\Framework\TestCase;

/**
 * BitBuffer coverage test
 */
final class BitBufferTest extends TestCase{

	protected BitBuffer $bitBuffer;

	protected function setUp():void{
		$this->bitBuffer = new BitBuffer;
	}

	public function bitProvider():array{
		return [
			'number'   => [Mode::DATA_NUMBER, 16],
			'alphanum' => [Mode::DATA_ALPHANUM, 32],
			'byte'     => [Mode::DATA_BYTE, 64],
			'kanji'    => [Mode::DATA_KANJI, 128],
		];
	}

	/**
	 * @dataProvider bitProvider
	 */
	public function testPut(int $data, int $value):void{
		$this->bitBuffer->put($data, 4);
		$this::assertSame($value, $this->bitBuffer->getBuffer()[0]);
		$this::assertSame(4, $this->bitBuffer->getLength());
	}

	public function testClear():void{
		$this->bitBuffer->clear();
		$this::assertSame([], $this->bitBuffer->getBuffer());
		$this::assertSame(0, $this->bitBuffer->getLength());
	}

}
