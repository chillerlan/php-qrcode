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

use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Data\MaskPatternTester;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCodeTest\QRTestAbstract;

class MaskPatternTesterTest extends QRTestAbstract{

	protected $FQCN = MaskPatternTester::class;

	// coverage
	public function testMaskpattern(){
		$matrix = (new Byte(new QROptions(['version' => 10]), 'test'))->initMatrix(0, true);

		$this->assertSame(6178, (new MaskPatternTester($matrix))->testPattern());
	}


}
