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
use PHPUnit\Framework\Attributes\{Test, TestWith};
use PHPUnit\Framework\TestCase;

/**
 * QROptions test
 */
final class QROptionsTest extends TestCase{

	/**
	 * Tests the $version clamping
	 */
	#[Test]
	#[TestWith([42, 40], 'values > 40 should be clamped to 40')]
	#[TestWith([-42, 1], 'values < 1 should be clamped to 1')]
	#[TestWith([21, 21], 'values in between should not be touched')]
	#[TestWith([Version::AUTO, -1], 'value -1 should be treated as is (default)')]
	public function versionClamp(int $version, int $expected):void{
		$o = new QROptions(['version' => $version]);

		$this::assertSame($expected, $o->version);
	}

	/**
	 * Tests the $versionMin/$versionMax clamping
	 */
	#[Test]
	#[TestWith([5, 10, 5, 10], 'normal clamp')]
	#[TestWith([-42, 42, 1, 40], 'exceeding values')]
	#[TestWith([10, 5, 5, 10], 'min > max' )]
	#[TestWith([42, -42, 1, 40], 'min > max, exceeding')]
	public function versionMinMaxClamp(int $versionMin, int $versionMax, int $expectedMin, int $expectedMax):void{
		$o = new QROptions(['versionMin' => $versionMin, 'versionMax' => $versionMax]);

		$this::assertSame($expectedMin, $o->versionMin);
		$this::assertSame($expectedMax, $o->versionMax);
	}

	/**
	 * Tests setting the ECC level from string or int
	 *
	 * @phan-suppress PhanTypeMismatchPropertyProbablyReal
	 */
	#[Test]
	public function setEccLevel():void{
		$o = new QROptions(['eccLevel' => EccLevel::H]);

		$this::assertSame(EccLevel::H, $o->eccLevel);
		/** @phpstan-ignore-next-line */
		$o->eccLevel = 'q';

		$this::assertSame(EccLevel::Q, $o->eccLevel);
	}

	/**
	 * Tests if an exception is thrown when attempting to set an invalid ECC level integer
	 */
	#[Test]
	public function setEccLevelFromIntException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid ECC level: "42"');

		new QROptions(['eccLevel' => 42]);
	}

	/**
	 * Tests if an exception is thrown when attempting to set an invalid ECC level string
	 */
	#[Test]
	public function setEccLevelFromStringException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid ECC level: "FOO"');

		new QROptions(['eccLevel' => 'foo']);
	}

	/**
	 * Tests the clamping (between 0 and 177) of the logo space values
	 */
	#[Test]
	#[TestWith([ -1,   0], 'negative')]
	#[TestWith([  0,   0], 'zero')]
	#[TestWith([ 69,  69], 'normal')]
	#[TestWith([177, 177], 'max')]
	#[TestWith([178, 177], 'exceed')]
	public function clampLogoSpaceValue(int $value, int $expected):void{
		$o = new QROptions;

		foreach(['logoSpaceWidth', 'logoSpaceHeight', 'logoSpaceStartX', 'logoSpaceStartY'] as $prop){
			$o->{$prop} = $value;
			$this::assertSame($expected, $o->{$prop});
		}

	}

	/**
	 * Tests if the optional logo space start values are nullable
	 */
	#[Test]
	public function logoSpaceStartNullable():void{
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
	 * Tests clamping of the circle radius
	 */
	#[Test]
	#[TestWith([0.0, 0.1], 'min')]
	#[TestWith([0.5, 0.5], 'no clamp')]
	#[TestWith([1.5, 0.75], 'max')]
	public function clampCircleRadius(float $value, float $expected):void{
		$o = new QROptions(['circleRadius' => $value]);

		$this::assertSame($expected, $o->circleRadius);
	}

}
