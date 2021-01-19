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

use chillerlan\QRCode\Data\{AlphaNum, QRCodeDataException};

/**
 * Tests the AlphaNum class
 */
final class AlphaNumTest extends DatainterfaceTestAbstract{

	/** @internal */
	protected array $testdata = [AlphaNum::class, '0 $%*+-./:'];

	/** @internal */
	protected array  $expected  = [
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
	 * Tests if an exception is thrown when an invalid character is encountered
	 */
	public function testGetCharCodeException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char: "#" [35]');

		$this->testdata = [AlphaNum::class, '#'];

		$this->setTestData();
	}

}
