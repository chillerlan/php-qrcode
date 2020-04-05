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
			'svg'  => [QRCode::OUTPUT_MARKUP_SVG, '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="qr-svg " style="width: 100%; height: auto;" viewBox="'],
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

		$expected = '000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111110101011110100001011110011111110000000010000010011111001010011111001010000010000000010111010111000001101011000001010111010000000010111010111010110000011010110010111010000000010111010100110101100000110101010111010000000010000010001101011000001101011010000010000000011111110101010101010101010101011111110000000000000000001010011111001010011000000000000000011110010101111010000101111010100111010000000001110001001011110100001011110110010010000000001100010011111001010011111001011110010000000011010000101000001101011000001011001010000000001101011010010110000011010110100000100000000000001001001110101100000110101101011100000000011100010100101011000001101011001100000000000000001000101100101001111100101111101010000000000111011111010111101000010111101100000000000001111000010000101111010000101101001110000000000100011110001111100101001111101000110000000010001001001101100000110101100110100010000000011100111001001101011000001101111011000000000010110101000000011010110000011011101100000000001111011110000110101100000110100001000000000010111100001111110010100111110100110100000000011001011111100001011110100001011010110000000000100101001101000010111101000000100110000000001011011100010100111110010100110011100000000010010101010011010110000011010000010010000000000111011101100000110101100001111110000000000000000000111011000001101011001000110110000000011111110000110000011010110011010111110000000010000010010010011111001010011000111100000000010111010010111010000101111011111101100000000010111010101011110100001011111100010010000000010111010111111001010011111011101010100000000010000010111000001101011000011001101000000000011111110111010110000011010110111100110000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';

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
