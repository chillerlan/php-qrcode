<?php
/**
 * Class ModeTest
 *
 * @created      25.07.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\Common\Mode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Mode coverage test
 */
final class ModeTest extends TestCase{

	/**
	 * version breakpoints for numeric mode
	 *
	 * @phpstan-return array<int, array{0: int, 1: int}>
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

	#[DataProvider('versionProvider')]
	public function testGetLengthBitsForVersionBreakpoints(int $version, int $expected):void{
		$this::assertSame($expected, Mode::getLengthBitsForVersion(Mode::NUMBER, $version));
	}

	public function testGetLengthBitsForVersionInvalidModeException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid mode given');

		Mode::getLengthBitsForVersion(42, 69);
	}

	public function testGetLengthBitsForVersionInvalidVersionException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid version number');

		Mode::getLengthBitsForVersion(Mode::BYTE, 69);
	}

}
