<?php
/**
 * Class QRCodeTest
 *
 * @filesource   QRCodeTest.php
 * @created      17.11.2017
 * @package      chillerlan\QRCodeTest
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\{QROptions, QRCode};
use chillerlan\QRCode\Data\{AlphaNum, Byte, Number, QRCodeDataException};
use chillerlan\QRCode\Output\QRCodeOutputException;
use chillerlan\QRCodeExamples\MyCustomOutput;

use function random_bytes;

class QRCodeTest extends QRTestAbstract{

	protected $FQCN = QRCode::class;

	/**
	 * @var \chillerlan\QRCode\QRCode
	 */
	protected $qrcode;

	protected function setUp():void{
		parent::setUp();

		$this->qrcode = $this->reflection->newInstance();
	}

	public function testIsNumber(){
		$this->assertTrue($this->qrcode->isNumber('0123456789'));
		$this->assertFalse($this->qrcode->isNumber('ABC'));
	}

	public function testIsAlphaNum(){
		$this->assertTrue($this->qrcode->isAlphaNum('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'));
		$this->assertFalse($this->qrcode->isAlphaNum('abc'));
	}

	public function testIsKanji(){
		$this->assertTrue($this->qrcode->isKanji('茗荷'));
		$this->assertFalse($this->qrcode->isKanji('Ã'));
	}

	// coverage

	public function typeDataProvider(){
		return [
			'png'  => [QRCode::OUTPUT_IMAGE_PNG, 'data:image/png;base64,'],
			'gif'  => [QRCode::OUTPUT_IMAGE_GIF, 'data:image/gif;base64,'],
			'jpg'  => [QRCode::OUTPUT_IMAGE_JPG, 'data:image/jpg;base64,'],
			'svg'  => [QRCode::OUTPUT_MARKUP_SVG, 'data:image/svg+xml;base64,'],
			'html' => [QRCode::OUTPUT_MARKUP_HTML, '<div><span style="background:'],
			'text' => [QRCode::OUTPUT_STRING_TEXT, '⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕'.PHP_EOL],
			'json' => [QRCode::OUTPUT_STRING_JSON, '[[18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18],'],
		];
	}

	/**
	 * @dataProvider typeDataProvider
	 * @param $type
	 */
	public function testRenderImage($type, $expected){
		$this->qrcode = $this->reflection->newInstanceArgs([new QROptions(['outputType' => $type])]);

		$this->assertStringContainsString($expected, $this->qrcode->render('test'));
	}

	public function testInitDataInterfaceException(){
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('invalid output type');

		(new QRCode(new QROptions(['outputType' => 'foo'])))->render('test');
	}

	public function testGetMatrixException(){
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('QRCode::getMatrix() No data given.');

		$this->qrcode->getMatrix('');
	}

	public function testTrim() {
		$m1 = $this->qrcode->getMatrix('hello');
		$m2 = $this->qrcode->getMatrix('hello '); // added space

		$this->assertNotEquals($m1, $m2);
	}

	public function testImageTransparencyBGDefault(){
		$this->qrcode = $this->reflection->newInstanceArgs([new QROptions(['imageTransparencyBG' => 'foo'])]);

		$this->assertSame([255,255,255], $this->getProperty('options')->getValue($this->qrcode)->imageTransparencyBG);
	}

	public function testCustomOutput(){

		$options = new QROptions([
			'version'         => 5,
			'eccLevel'        => QRCode::ECC_L,
			'outputType'      => QRCode::OUTPUT_CUSTOM,
			'outputInterface' => MyCustomOutput::class,
		]);

		$expected = '000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111110111010000101111010000011111110000000010000010111000001101011000001010000010000000010111010101101011000001101011010111010000000010111010110100111110010100111010111010000000010111010000001101011000001101010111010000000010000010100111110010100111110010000010000000011111110101010101010101010101011111110000000000000000010010100111110010100000000000000000011001110000101111010000101111001011110000000000000000111010000101111010000111100010000000001011010100111110010100111110011001010000000010000101111101011000001101011110011110000000000011010100011000001101011000101110100000000011001100001001101011000001101010011010000000010110111110000001101011000001100110100000000010000100100010100111110010100001100100000000011111110111101111010000101111010100110000000011010000111010000101111010000111100100000000010101111111111110010100111110011001000000000010110001110101011000001101011110011010000000001001111100011000001101011000101110010000000011000100110001101011000001101010011100000000001000011001000001101011000001100110000000000011101001011010100111110010100001100000000000010111010001101111010000101111010100110000000011100000001010000101111010000111100000000000000001110110111110010100111110011001000000000000011001011101011000001101011110011100000000011111110101011000001101011001111110110000000000000000110001101011000001101000111100000000011111110001000001101011000011010110000000000010000010101010100111110010101000100100000000010111010111101111010000101111111100110000000010111010011010000101111010001101100010000000010111010000111110010100111100101101100000000010000010101101011000001101001100111100000000011111110101011000001101011000110010110000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';

		$this->assertSame($expected, $this->reflection->newInstanceArgs([$options])->render('test'));
	}

	public function testDataModeOverride(){
		$this->qrcode = $this->reflection->newInstance();

		$this->assertInstanceOf(Number::class, $this->qrcode->initDataInterface('123'));
		$this->assertInstanceOf(AlphaNum::class, $this->qrcode->initDataInterface('ABC123'));
		$this->assertInstanceOf(Byte::class, $this->qrcode->initDataInterface(random_bytes(32)));

		$this->qrcode = $this->reflection->newInstanceArgs([new QROptions(['dataMode' => 'Byte'])]);

		$this->assertInstanceOf(Byte::class, $this->qrcode->initDataInterface('123'));
		$this->assertInstanceOf(Byte::class, $this->qrcode->initDataInterface('ABC123'));
		$this->assertInstanceOf(Byte::class, $this->qrcode->initDataInterface(random_bytes(32)));
	}

	public function testDataModeOverrideError(){
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char:');

		$this->qrcode = $this->reflection->newInstanceArgs([new QROptions(['dataMode' => 'AlphaNum'])]);

		$this->qrcode->initDataInterface(random_bytes(32));
	}

}
