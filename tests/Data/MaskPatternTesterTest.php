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

use chillerlan\QRCode\{QROptions, Data\Byte, Data\MaskPatternTester};
use chillerlan\QRCodeTest\QRTestAbstract;

class MaskPatternTesterTest extends QRTestAbstract{

	protected string $FQCN = MaskPatternTester::class;

	// coverage
	public function testMaskpattern():void{
		$dataInterface = new Byte(new QROptions(['version' => 10]), 'test');

		$this::assertSame(4, (new MaskPatternTester($dataInterface))->getBestMaskPattern());
	}

	public function testMaskpatternID():void{
		$dataInterface = new Byte(new QROptions(['version' => 10]), 'test');

		$this::assertSame(6178, (new MaskPatternTester($dataInterface))->testPattern(0));
	}

}
