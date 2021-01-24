<?php
/**
 * Class MaskPatternTesterTest
 *
 * @created      24.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Common\MaskPattern;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\{Byte, MaskPatternTester, QRData};
use PHPUnit\Framework\TestCase;

/**
 * MaskPatternTester coverage test
 */
final class MaskPatternTesterTest extends TestCase{

	/**
	 * Tests getting the best mask pattern
	 */
	public function testMaskpattern():void{
		$dataInterface = new QRData(new QROptions(['version' => 10]), [new Byte('test')]);

		$this::assertSame(3, (new MaskPatternTester($dataInterface))->getBestMaskPattern()->getPattern());
	}

	/**
	 * Tests getting the penalty value for a given mask pattern
	 */
	public function testMaskpatternID():void{
		$dataInterface = new QRData(new QROptions(['version' => 10]), [new Byte('test')]);

		$this::assertSame(4243, (new MaskPatternTester($dataInterface))->testPattern(new MaskPattern(MaskPattern::PATTERN_011)));
	}

}
