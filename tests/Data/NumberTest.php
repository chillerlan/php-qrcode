<?php
/**
 * Class NumberTest
 *
 * @filesource   NumberTest.php
 * @created      24.11.2017
 * @package      chillerlan\QRCodeTest\Data
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\Data\Number;

class NumberTest extends DatainterfaceTestAbstract{

	protected $FQCN = Number::class;
	protected $testdata  = '0123456789';
	protected $expected = [
		16, 40, 12, 86, 106, 105, 0, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		201, 141, 102, 116, 238, 162, 239, 230,
		222, 37, 79, 192, 42, 109, 188, 72,
		89, 63, 168, 151
	];

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage illegal char: "#" [35]
	 */
	public function testGetCharCodeException(){
		$this->dataInterface->setData('#');
	}

}
