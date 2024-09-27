<?php
/**
 * Class MaskPatternTest
 *
 * @created      21.11.2021
 * @author       ZXing Authors
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      Apache-2.0
 *
 * @codingStandardsIgnoreFile Squiz.Arrays.ArrayDeclaration.NoCommaAfterLast
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Common;

use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\Common\MaskPattern;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Closure;

/**
 * @see https://github.com/zxing/zxing/blob/f4f3c2971dc794346d8b6e14752200008cb90716/core/src/test/java/com/google/zxing/qrcode/encoder/MaskUtilTestCase.java
 */
final class MaskPatternTest extends TestCase{

	// See mask patterns on the page 43 of JISX0510:2004.
	public static function maskPatternProvider():array{
		return [
			'PATTERN_000' => [MaskPattern::PATTERN_000, [
				[1, 0, 1, 0, 1, 0],
				[0, 1, 0, 1, 0, 1],
				[1, 0, 1, 0, 1, 0],
				[0, 1, 0, 1, 0, 1],
				[1, 0, 1, 0, 1, 0],
				[0, 1, 0, 1, 0, 1],
			]],
			'PATTERN_001' => [MaskPattern::PATTERN_001, [
				[1, 1, 1, 1, 1, 1],
				[0, 0, 0, 0, 0, 0],
				[1, 1, 1, 1, 1, 1],
				[0, 0, 0, 0, 0, 0],
				[1, 1, 1, 1, 1, 1],
				[0, 0, 0, 0, 0, 0],
			]],
			'PATTERN_010' => [MaskPattern::PATTERN_010, [
				[1, 0, 0, 1, 0, 0],
				[1, 0, 0, 1, 0, 0],
				[1, 0, 0, 1, 0, 0],
				[1, 0, 0, 1, 0, 0],
				[1, 0, 0, 1, 0, 0],
				[1, 0, 0, 1, 0, 0],
			]],
			'PATTERN_011' => [MaskPattern::PATTERN_011, [
				[1, 0, 0, 1, 0, 0],
				[0, 0, 1, 0, 0, 1],
				[0, 1, 0, 0, 1, 0],
				[1, 0, 0, 1, 0, 0],
				[0, 0, 1, 0, 0, 1],
				[0, 1, 0, 0, 1, 0],
			]],
			'PATTERN_100' => [MaskPattern::PATTERN_100, [
				[1, 1, 1, 0, 0, 0],
				[1, 1, 1, 0, 0, 0],
				[0, 0, 0, 1, 1, 1],
				[0, 0, 0, 1, 1, 1],
				[1, 1, 1, 0, 0, 0],
				[1, 1, 1, 0, 0, 0],
			]],
			'PATTERN_101' => [MaskPattern::PATTERN_101, [
				[1, 1, 1, 1, 1, 1],
				[1, 0, 0, 0, 0, 0],
				[1, 0, 0, 1, 0, 0],
				[1, 0, 1, 0, 1, 0],
				[1, 0, 0, 1, 0, 0],
				[1, 0, 0, 0, 0, 0],
			]],
			'PATTERN_110' => [MaskPattern::PATTERN_110, [
				[1, 1, 1, 1, 1, 1],
				[1, 1, 1, 0, 0, 0],
				[1, 1, 0, 1, 1, 0],
				[1, 0, 1, 0, 1, 0],
				[1, 0, 1, 1, 0, 1],
				[1, 0, 0, 0, 1, 1],
			]],
			'PATTERN_111' => [MaskPattern::PATTERN_111, [
				[1, 0, 1, 0, 1, 0],
				[0, 0, 0, 1, 1, 1],
				[1, 0, 0, 0, 1, 1],
				[0, 1, 0, 1, 0, 1],
				[1, 1, 1, 0, 0, 0],
				[0, 1, 1, 1, 0, 0],
			]],
		];
	}

	/**
	 * Tests if the mask function generates the correct pattern
	 */
	#[DataProvider('maskPatternProvider')]
	public function testMask(int $pattern, array $expected):void{
		$maskPattern = new MaskPattern($pattern);

		$this::assertTrue($this->assertMask($maskPattern->getMask(), $expected));
	}

	private function assertMask(Closure $mask, array $expected):bool{

		for($x = 0; $x < 6; $x++){
			for($y = 0; $y < 6; $y++){
				if($mask($x, $y) !== ($expected[$y][$x] === 1)){
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Tests if an exception is thrown on an incorrect mask pattern
	 */
	public function testInvalidMaskPatternException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('invalid mask pattern');

		new MaskPattern(42);
	}

	public function testPenaltyRule1():void{
		// horizontal
		$this::assertSame(0, MaskPattern::testRule1([[false, false, false, false]], 1, 4));
		$this::assertSame(3, MaskPattern::testRule1([[false, false, false, false, false, true]], 1, 6));
		$this::assertSame(4, MaskPattern::testRule1([[false, false, false, false, false, false]], 1, 6));
		// vertical
		$this::assertSame(0, MaskPattern::testRule1([[false], [false], [false], [false]], 4, 1));
		$this::assertSame(3, MaskPattern::testRule1([[false], [false], [false], [false], [false], [true]], 6, 1));
		$this::assertSame(4, MaskPattern::testRule1([[false], [false], [false], [false], [false], [false]], 6, 1));
	}

	public function testPenaltyRule2():void{
		$this::assertSame(0, MaskPattern::testRule2([[false]], 1, 1));
		$this::assertSame(0, MaskPattern::testRule2([[false, false], [false, true]], 2, 2));
		$this::assertSame(3, MaskPattern::testRule2([[false, false], [false, false]], 2, 2));
		$this::assertSame(12, MaskPattern::testRule2([[false, false, false], [false, false, false], [false, false, false]], 3, 3));
	}

	public function testPenaltyRule3():void{
		// horizontal
		$this::assertSame(40, MaskPattern::testRule3([[false, false, false, false, true, false, true, true, true, false, true]], 1, 11));
		$this::assertSame(40, MaskPattern::testRule3([[true, false, true, true, true, false, true, false, false, false, false]], 1, 11));
		$this::assertSame(0, MaskPattern::testRule3([[true, false, true, true, true, false, true]], 1, 7));
		// vertical
		$this::assertSame(40, MaskPattern::testRule3([[false], [false], [false], [false], [true], [false], [true], [true], [true], [false], [true]], 11, 1));
		$this::assertSame(40, MaskPattern::testRule3([[true], [false], [true], [true], [true], [false], [true], [false], [false], [false], [false]], 11, 1));
		$this::assertSame(0, MaskPattern::testRule3([[true], [false], [true], [true], [true], [false], [true]], 7, 1));
	}

	public function testPenaltyRule4():void{
		$this::assertSame(100, MaskPattern::testRule4([[false]], 1, 1));
		$this::assertSame(0, MaskPattern::testRule4([[false, true]], 1, 2));
		$this::assertSame(30, MaskPattern::testRule4([[false, true, true, true, true, false]], 1, 6));
	}
}
