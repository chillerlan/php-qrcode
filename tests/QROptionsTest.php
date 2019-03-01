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

use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use PHPUnit\Framework\TestCase;

class QROptionsTest extends TestCase{

	/**
	 * @var \chillerlan\QRCode\QROptions
	 */
	protected $options;

	public function testVersionClamp(){
		$this->assertSame(40, (new QROptions(['version' => 42]))->version);
		$this->assertSame(1, (new QROptions(['version' => -42]))->version);
		$this->assertSame(21, (new QROptions(['version' => 21]))->version);
		$this->assertSame(QRCode::VERSION_AUTO, (new QROptions)->version); // QRCode::VERSION_AUTO = -1, default
	}

	public function testVersionMinMaxClamp(){
		// normal clamp
		$o = new QROptions(['versionMin' => 5, 'versionMax' => 10]);
		$this->assertSame(5, $o->versionMin);
		$this->assertSame(10, $o->versionMax);

		// exceeding values
		$o = new QROptions(['versionMin' => -42, 'versionMax' => 42]);
		$this->assertSame(1, $o->versionMin);
		$this->assertSame(40, $o->versionMax);

		// min > max
		$o = new QROptions(['versionMin' => 10, 'versionMax' => 5]);
		$this->assertSame(5, $o->versionMin);
		$this->assertSame(10, $o->versionMax);

		$o = new QROptions(['versionMin' => 42, 'versionMax' => -42]);
		$this->assertSame(1, $o->versionMin);
		$this->assertSame(40, $o->versionMax);
	}

	public function testMaskPatternClamp(){
		$this->assertSame(7, (new QROptions(['maskPattern' => 42]))->maskPattern);
		$this->assertSame(0, (new QROptions(['maskPattern' => -42]))->maskPattern);
		$this->assertSame(QRCode::MASK_PATTERN_AUTO, (new QROptions)->maskPattern); // QRCode::MASK_PATTERN_AUTO = -1, default
	}

	public function testInvalidEccLevelException(){
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid error correct level: 42');

		new QROptions(['eccLevel' => 42]);
	}

	public function testClampRGBValues(){
		$o = new QROptions(['imageTransparencyBG' => [-1, 0, 999]]);

		$this->assertSame(0, $o->imageTransparencyBG[0]);
		$this->assertSame(0, $o->imageTransparencyBG[1]);
		$this->assertSame(255, $o->imageTransparencyBG[2]);
	}

	public function testInvalidRGBValueException(){
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid RGB value.');

		new QROptions(['imageTransparencyBG' => ['r', 'g', 'b']]);
	}
}
