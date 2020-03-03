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

use chillerlan\QRCode\Data\{Kanji, QRCodeDataException};

use function str_repeat;

class KanjiTest extends DatainterfaceTestAbstract{

	protected string $FQCN = Kanji::class;

	protected string $testdata = '茗荷茗荷茗荷茗荷茗荷';

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

	public function testIllegalCharException1():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char at 1 [16191]');

		$this->dataInterface->setData('ÃÃ');
	}

	public function testIllegalCharException2():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char at 1');

		$this->dataInterface->setData('Ã');
	}

	public function testCodeLengthOverflowException():void{
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('code length overflow');

		$this->dataInterface->setData(str_repeat('荷', 1337));
	}

}
