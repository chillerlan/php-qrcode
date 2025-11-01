<?php
/**
 * Class BitBufferTest
 *
 * @created      08.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\Common\{BitBuffer, Mode};
use PHPUnit\Framework\Attributes\{Test, TestWith};
use PHPUnit\Framework\TestCase;

/**
 * BitBuffer coverage test
 */
final class BitBufferTest extends TestCase{

	private BitBuffer $bitBuffer;

	protected function setUp():void{
		$this->bitBuffer = new BitBuffer;
	}

	#[Test]
	#[TestWith([Mode::NUMBER, 16], 'number')]
	#[TestWith([Mode::ALPHANUM, 32], 'alphanum')]
	#[TestWith([Mode::BYTE, 64], 'byte')]
	#[TestWith([Mode::KANJI, 128], 'kanji')]
	#[TestWith([Mode::HANZI, 208], 'hanzi')]
	public function put(int $data, int $expected):void{
		$this->bitBuffer->put($data, 4);

		$this::assertSame($expected, $this->bitBuffer->getBuffer()[0]);
		$this::assertSame(4, $this->bitBuffer->getLength());
	}

	#[Test]
	public function readException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid $numBits');

		$this->bitBuffer->put(Mode::KANJI, 4);
		$this->bitBuffer->read($this->bitBuffer->available() + 1);
	}

}
