<?php
/**
 * Class QROptionsTest
 *
 * @filesource   QROptionsTest.php
 * @created      08.11.2018
 * @package      chillerlan\QRCodeTest
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\Settings\SettingsContainerInterface;
use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use PHPUnit\Framework\TestCase;

class QROptionsTest extends TestCase{

	/** @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\QRCode\QROptions */
	protected SettingsContainerInterface $options;

	public function testVersionClamp():void{
		$o = new QROptions(['version' => 42]);
		$this::assertSame(40, $o->version);

		$o = new QROptions(['version' => -42]);
		$this::assertSame(1, $o->version);

		$o = new QROptions(['version' => 21]);
		$this::assertSame(21, $o->version);

		// QRCode::VERSION_AUTO = -1, default
		$o = new QROptions;
		$this::assertSame(QRCode::VERSION_AUTO, $o->version);
	}

	public function testVersionMinMaxClamp():void{
		// normal clamp
		$o = new QROptions(['versionMin' => 5, 'versionMax' => 10]);
		$this::assertSame(5, $o->versionMin);
		$this::assertSame(10, $o->versionMax);

		// exceeding values
		$o = new QROptions(['versionMin' => -42, 'versionMax' => 42]);
		$this::assertSame(1, $o->versionMin);
		$this::assertSame(40, $o->versionMax);

		// min > max
		$o = new QROptions(['versionMin' => 10, 'versionMax' => 5]);
		$this::assertSame(5, $o->versionMin);
		$this::assertSame(10, $o->versionMax);

		$o = new QROptions(['versionMin' => 42, 'versionMax' => -42]);
		$this::assertSame(1, $o->versionMin);
		$this::assertSame(40, $o->versionMax);
	}

	public function testMaskPatternClamp():void{
		$o = new QROptions(['maskPattern' => 42]);
		$this::assertSame(7, $o->maskPattern);

		$o = new QROptions(['maskPattern' => -42]);
		$this::assertSame(0, $o->maskPattern);

		// QRCode::MASK_PATTERN_AUTO = -1, default
		$o = new QROptions;
		$this::assertSame(QRCode::MASK_PATTERN_AUTO, $o->maskPattern);
	}

	public function testInvalidEccLevelException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid error correct level: 42');

		/** @noinspection PhpUnusedLocalVariableInspection */
		$o = new QROptions(['eccLevel' => 42]);
	}

	public function testClampRGBValues():void{
		$o = new QROptions(['imageTransparencyBG' => [-1, 0, 999]]);

		$this::assertSame(0, $o->imageTransparencyBG[0]);
		$this::assertSame(0, $o->imageTransparencyBG[1]);
		$this::assertSame(255, $o->imageTransparencyBG[2]);
	}

	public function testInvalidRGBValueException():void{
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid RGB value.');

		/** @noinspection PhpUnusedLocalVariableInspection */
		$o = new QROptions(['imageTransparencyBG' => ['r', 'g', 'b']]);
	}
}
