<?php
/**
 * Class AlphaNumTest
 *
 * @filesource   AlphaNumTest.php
 * @created      24.11.2017
 * @package      chillerlan\QRCodeTest\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\AlphaNum;

class AlphaNumTest extends DatainterfaceTestAbstract{

	protected $FQCN = AlphaNum::class;
	protected $testdata  = '0 $%*+-./:';
	protected $expected  = [
		32, 80, 36, 212, 252, 15, 175, 251,
		176, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		112, 43, 9, 248, 200, 194, 75, 25,
		205, 173, 154, 68, 191, 16, 128,
		92, 112, 20, 198, 27
	];

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char: "#" [35]
	 */
	public function testGetCharCodeException(){
		$this->dataInterface->setData('#');
	}

}
