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
use PHPUnit\Framework\Attributes\{Test, TestWith};
use PHPUnit\Framework\TestCase;

/**
 * Mode coverage test
 */
final class ModeTest extends TestCase{

	/**
	 * Tests the version breakpoints for numeric mode
	 */
	#[Test]
	#[TestWith([ 1, 10], '10 low')]
	#[TestWith([ 9, 10], '10 high')]
	#[TestWith([10, 12], '12 low')]
	#[TestWith([26, 12], '12 high')]
	#[TestWith([27, 14], '14 low')]
	#[TestWith([40, 14], '14 high')]
	public function getLengthBitsForVersionBreakpoints(int $version, int $expected):void{
		$this::assertSame($expected, Mode::getLengthBitsForVersion(Mode::NUMBER, $version));
	}

	#[Test]
	public function getLengthBitsForVersionInvalidModeException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid mode given');

		Mode::getLengthBitsForVersion(42, 69);
	}

	#[Test]
	public function getLengthBitsForVersionInvalidVersionException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid version number');

		Mode::getLengthBitsForVersion(Mode::BYTE, 69);
	}

}
