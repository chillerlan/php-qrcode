<?php
/**
 * Class ModeTest
 *
 * @created      25.07.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\Common\Mode;
use chillerlan\QRCode\QRCodeException;
use PHPUnit\Framework\TestCase;

/**
 * Mode coverage test
 */
final class ModeTest extends TestCase{

	/**
	 * version breakpoints for numeric mode
	 */
	public static function versionProvider():array{
		return [
			[ 1, 10],
			[ 9, 10],
			[10, 12],
			[26, 12],
			[27, 14],
			[40, 14],
		];
	}

	/**
	 * @dataProvider versionProvider
	 */
	public function testGetLengthBitsForVersionBreakpoints(int $version, int $expected):void{
		$this::assertSame($expected, Mode::getLengthBitsForVersion(Mode::NUMBER, $version));
	}

	public function testGetLengthBitsForVersionInvalidModeException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid mode given');
		/** @phan-suppress-next-line PhanNoopNew */
		Mode::getLengthBitsForVersion(42, 69);
	}

	public function testGetLengthBitsForVersionInvalidVersionException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid version number');
		/** @phan-suppress-next-line PhanNoopNew */
		Mode::getLengthBitsForVersion(Mode::BYTE, 69);
	}

}
