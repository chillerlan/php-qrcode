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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\{Kanji, QRCodeDataException, QRDataInterface};

/**
 * Tests the Kanji class
 */
final class KanjiTest extends DatainterfaceTestAbstract{

	/** @internal */
	protected string $testdata = '茗荷茗荷茗荷茗荷茗荷';

	/** @internal */
	protected array  $expected = [
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
	 * @inheritDoc
	 * @internal
	 */
	protected function getDataInterfaceInstance(QROptions $options):QRDataInterface{
		return new Kanji($options);
	}

	/**
	 * Tests if an exception is thrown when an invalid character is encountered
	 */
	public function testIllegalCharException1():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char at 1 [16191]');

		$this->dataInterface->setData('ÃÃ');
	}

	/**
	 * Tests if an exception is thrown when an invalid character is encountered
	 */
	public function testIllegalCharException2():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char at 1');

		$this->dataInterface->setData('Ã');
	}

}
