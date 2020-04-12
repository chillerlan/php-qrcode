<?php
/**
 * Class MaskPatternTesterTest
 *
 * @filesource   MaskPatternTesterTest.php
 * @created      24.11.2017
 * @package      chillerlan\QRCodeTest\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\{Byte, MaskPatternTester};
use PHPUnit\Framework\TestCase;

/**
 * MaskPatternTester coverage test
 */
final class MaskPatternTesterTest extends TestCase{

	/**
	 * Tests getting the best mask pattern
	 */
	public function testMaskpattern():void{
		$dataInterface = new Byte(new QROptions(['version' => 10]), 'test');

		$this::assertSame(3, (new MaskPatternTester($dataInterface))->getBestMaskPattern());
	}

	/**
	 * Tests getting the penalty value for a given mask pattern
	 */
	public function testMaskpatternID():void{
		$dataInterface = new Byte(new QROptions(['version' => 10]), 'test');

		$this::assertSame(4243, (new MaskPatternTester($dataInterface))->testPattern(3));
	}

}
