<?php
/**
 * Class QROptionsTest
 *
 * @created      08.11.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnusedLocalVariableInspection
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\{QRCodeException, QROptions};
use chillerlan\QRCode\Common\{EccLevel, Version};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * QROptions test
 */
final class QROptionsTest extends TestCase{

	/**
	 * @return int[][]
	 */
	public static function VersionProvider():array{
		return [
			'values > 40 should be clamped to 40'        => [42, 40],
			'values < 1 should be clamped to 1'          => [-42, 1],
			'values in between shold not be touched'     => [21, 21],
			'value -1 should be treated as is (default)' => [Version::AUTO, -1],
		];
	}

	/**
	 * Tests the $version clamping
	 */
	#[DataProvider('VersionProvider')]
	public function testVersionClamp(int $version, int $expected):void{
		$o = new QROptions(['version' => $version]);

		$this::assertSame($expected, $o->version);
	}

	/**
	 * @return int[][]
	 */
	public static function VersionMinMaxProvider():array{
		return [
			'normal clamp'         => [5, 10, 5, 10],
			'exceeding values'     => [-42, 42, 1, 40],
			'min > max'            => [10, 5, 5, 10],
			'min > max, exceeding' => [42, -42, 1, 40],
		];
	}

	/**
	 * Tests the $versionMin/$versionMax clamping
	 */
	#[DataProvider('VersionMinMaxProvider')]
	public function testVersionMinMaxClamp(int $versionMin, int $versionMax, int $expectedMin, int $expectedMax):void{
		$o = new QROptions(['versionMin' => $versionMin, 'versionMax' => $versionMax]);

		$this::assertSame($expectedMin, $o->versionMin);
		$this::assertSame($expectedMax, $o->versionMax);
	}

	/**
	 * Tests setting the ECC level from string or int
	 */
	public function testSetEccLevel():void{
		$o = new QROptions(['eccLevel' => EccLevel::H]);

		$this::assertSame(EccLevel::H, $o->eccLevel);
		/** @phpstan-ignore-next-line */
		$o->eccLevel = 'q';

		$this::assertSame(EccLevel::Q, $o->eccLevel);
	}

	/**
	 * Tests if an exception is thrown when attempting to set an invalid ECC level integer
	 */
	public function testSetEccLevelFromIntException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid ECC level: "42"');

		new QROptions(['eccLevel' => 42]);
	}

	/**
	 * Tests if an exception is thrown when attempting to set an invalid ECC level string
	 */
	public function testSetEccLevelFromStringException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid ECC level: "FOO"');

		new QROptions(['eccLevel' => 'foo']);
	}

	/**
	 * @return int[][][]
	 */
	public static function RGBProvider():array{
		return [
			'exceeding values' => [[-1, 0, 999], [0, 0 ,255]],
			'too few values'   => [[1, 2], [255, 255, 255]],
			'too many values'  => [[1, 2, 3, 4, 5], [1, 2, 3]],
		];
	}

	/**
	 * @return int[][]
	 */
	public static function logoSpaceValueProvider():array{
		return [
			'negative' => [ -1,   0],
			'zero'     => [  0,   0],
			'normal'   => [ 69,  69],
			'max'      => [177, 177],
			'exceed'   => [178, 177],
		];
	}

	/**
	 * Tests the clamping (between 0 and 177) of the logo space values
	 */
	#[DataProvider('logoSpaceValueProvider')]
	public function testClampLogoSpaceValue(int $value, int $expected):void{
		$o = new QROptions;

		foreach(['logoSpaceWidth', 'logoSpaceHeight', 'logoSpaceStartX', 'logoSpaceStartY'] as $prop){
			$o->{$prop} = $value;
			$this::assertSame($expected, $o->{$prop});
		}

	}

	/**
	 * Tests if the optional logo space start values are nullable
	 */
	public function testLogoSpaceStartNullable():void{
		$o = new QROptions([
			'logoSpaceStartX' => 42,
			'logoSpaceStartY' => 69,
		]);

		$this::assertSame(42, $o->logoSpaceStartX);
		$this::assertSame(69, $o->logoSpaceStartY);

		$o->logoSpaceStartX = null;
		$o->logoSpaceStartY = null;

		$this::assertNull($o->logoSpaceStartX);
		$this::assertNull($o->logoSpaceStartY);
	}

	/**
	 * @return float[][]
	 */
	public static function circleRadiusProvider():array{
		return [
			[0.0, 0.1],
			[0.5, 0.5],
			[1.5, 0.75],
		];
	}

	/**
	 * Tests clamping of the circle radius
	 */
	#[DataProvider('circleRadiusProvider')]
	public function testClampCircleRadius(float $value, float $expected):void{
		$o = new QROptions(['circleRadius' => $value]);

		$this::assertSame($expected, $o->circleRadius);
	}

}
