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
use chillerlan\QRCode\QRCodeException;
use PHPUnit\Framework\TestCase;

/**
 * BitBuffer coverage test
 */
final class BitBufferTest extends TestCase{

	private BitBuffer $bitBuffer;

	protected function setUp():void{
		$this->bitBuffer = new BitBuffer;
	}

	public static function bitProvider():array{
		return [
			'number'   => [Mode::NUMBER, 16],
			'alphanum' => [Mode::ALPHANUM, 32],
			'byte'     => [Mode::BYTE, 64],
			'kanji'    => [Mode::KANJI, 128],
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

	public function testReadException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid $numBits');

		$this->bitBuffer->put(Mode::KANJI, 4);
		$this->bitBuffer->read($this->bitBuffer->available() + 1);
	}

}
