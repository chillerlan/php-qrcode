<?php
/**
 * Class KanjiTest
 *
 * @filesource   KanjiTest.php
 * @created      24.11.2017
 * @package      chillerlan\QRCodeTest\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Kanji;

class KanjiTest extends DatainterfaceTestAbstract{

	protected $FQCN = Kanji::class;
	protected $testdata = '茗荷茗荷茗荷茗荷茗荷';
	protected $expected = [
		128, 173, 85, 26, 95, 85, 70, 151,
		213, 81, 165, 245, 84, 105, 125, 85,
		26, 92, 0, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		195, 11, 221, 91, 141, 220, 163, 46,
		165, 37, 163, 176, 79, 0, 64, 68,
		96, 113, 54, 191
	];

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char at 1 [16191]
	 */
	public function testIllegalCharException1(){
		$this->dataInterface->setData('ÃÃ');
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char at 1
	 */
	public function testIllegalCharException2(){
		$this->dataInterface->setData('Ã');
	}
}
