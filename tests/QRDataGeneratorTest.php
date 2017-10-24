<?php
/**
 * Class QRDataGeneratorTest
 *
 * @filesource   QRDataGeneratorTest.php
 * @created      24.10.2017
 * @package      chillerlan\QRCodeTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\Data\AlphaNum;
use chillerlan\QRCode\Data\QRDataInterface;
use chillerlan\QRCode\Output\QRString;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRDataGenerator;
use chillerlan\QRCode\QROptions;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class QRDataGeneratorTest extends TestCase{

	/**
	 * @var \chillerlan\QRCode\QRDataGenerator
	 */
	protected $qrTest;

	/**
	 * @var \ReflectionClass
	 */
	protected $reflectionClass;


	protected function setUp(){
		$this->reflectionClass = new ReflectionClass(QRDataGenerator::class);

		$this->qrTest = new QRDataGenerator('test', QRCode::TYPE_05, QRCode::ERROR_CORRECT_LEVEL_L);
	}

	private function getMethod(string $method):ReflectionMethod {
		$method = $this->reflectionClass->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	private function getProperty(string $property):ReflectionProperty{
		$property = $this->reflectionClass->getProperty($property);
		$property->setAccessible(true);

		return $property;
	}

	public function stringDataProvider(){
		return [
			['1234567890', QRCode::TYPE_01],
			['ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', QRCode::TYPE_03],
			['#\\', QRCode::TYPE_01],
			['茗荷', QRCode::TYPE_01],
		];
	}

	/**
	 * @dataProvider stringDataProvider
	 */
	public function testTypeAutoOverride($data, $type){
		$qrcode = $this->reflectionClass->newInstanceArgs([$data, $type, QRCode::ERROR_CORRECT_LEVEL_L]);

		$this->assertSame($type, $this->getProperty('typeNumber')->getValue($qrcode));
	}

	public function getMatrixDataProvider(){
		return [
			[QRCode::TYPE_01, 'foobar', 21],
			[QRCode::TYPE_05, 'foobar', 37],
			[QRCode::TYPE_10, 'foobar', 57],
			[QRCode::TYPE_05, '1234567890', 37],
			[QRCode::TYPE_10, '1234567890', 57],
			[QRCode::TYPE_03, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', 29],
			[QRCode::TYPE_05, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', 37],
			[QRCode::TYPE_10, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', 57],
			[QRCode::TYPE_05, '茗荷', 37],
			[QRCode::TYPE_10, '茗荷', 57],
		];
	}

	/**
	 * @dataProvider getMatrixDataProvider
	 */
	public function testInternalGetMatrix($type, $data, $pixelCount){
		$method   = $this->getMethod('getMatrix');
		$property = $this->getProperty('pixelCount');

		for($i = 0; $i <= 7; $i++){
			$qrcode = $this->reflectionClass->newInstanceArgs([$data, $type, QRCode::ERROR_CORRECT_LEVEL_L]);
			$method->invokeArgs($qrcode, [false, $i]);

			$this->assertSame($pixelCount, $property->getValue($qrcode));
		}
	}

	public function testIsNumber(){
		$this->assertTrue($this->qrTest->isNumber('1234567890'));
		$this->assertFalse($this->qrTest->isNumber('abc'));
	}

	public function testIsAlphaNum(){
		$this->assertTrue($this->qrTest->isAlphaNum('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'));
		$this->assertFalse($this->qrTest->isAlphaNum('#'));
	}

	// http://stackoverflow.com/a/24755772
	public function testIsKanji(){
		$this->assertTrue($this->qrTest->isKanji('茗荷'));
		$this->assertFalse($this->qrTest->isKanji(''));
		$this->assertFalse($this->qrTest->isKanji('ÃÃÃ')); // non-kanji
		$this->assertFalse($this->qrTest->isKanji('荷')); // kanji forced into byte mode due to length
	}

	// coverage
	public function testGetBCHTypeNumber(){
		$this->assertSame(7973, $this->getMethod('getBCHTypeNumber')->invokeArgs($this->qrTest, [1]));
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage $typeNumber: 1 / $errorCorrectLevel: 42
	 */
	public function testGetRSBlocksException(){
		$this->getMethod('getRSBlocks')->invokeArgs($this->qrTest, [1, 42]);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid error correct level: 42
	 */
	public function testGetMaxLengthECLevelException(){
		$this->getMethod('getMaxLength')->invokeArgs($this->qrTest, [QRCode::TYPE_01, QRDataInterface::MODE_BYTE, 42]);
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid mode: 1337
	 */
	public function testGetMaxLengthModeException(){
		$this->getMethod('getMaxLength')->invokeArgs($this->qrTest, [QRCode::TYPE_01, 1337, QRCode::ERROR_CORRECT_LEVEL_H]);
	}

	public function getTypeNumberDataProvider(){
		return [
			[QRDataInterface::MODE_ALPHANUM, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:', 2],
			[QRDataInterface::MODE_BYTE, '#\\', 1],
			[QRDataInterface::MODE_KANJI, '茗荷', 1],
			[QRDataInterface::MODE_NUMBER, '1234567890', 1],
		];
	}

	/**
	 * @dataProvider getTypeNumberDataProvider
	 */
	public function testGetTypeNumber($mode, $data, $expected){
		$i = QRDataGenerator::DATA_INTERFACES[$mode];

		$this->assertSame($expected, $this->getMethod('getTypeNumber')->invokeArgs($this->qrTest, [new $i($data), $mode]));
	}

}
