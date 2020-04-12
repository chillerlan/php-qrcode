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

	protected $FQCN = MaskPatternTester::class;

	// coverage
	public function testMaskpattern(){
		$matrix = (new Byte(new QROptions(['version' => 10]), 'test'))->initMatrix(3, true);

		$this->assertSame(4243, (new MaskPatternTester($matrix))->testPattern());
	}

}
