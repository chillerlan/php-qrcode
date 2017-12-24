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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;

class QRCodeTest extends QRTestAbstract{

	protected $FQCN = QRCode::class;

	/**
	 * @var \chillerlan\QRCode\QRCode
	 */
	protected $qrcode;

	protected function setUp(){
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
			[QRCode::OUTPUT_IMAGE_PNG, 'data:image/png;base64,'],
			[QRCode::OUTPUT_IMAGE_GIF, 'data:image/gif;base64,'],
			[QRCode::OUTPUT_IMAGE_JPG, 'data:image/jpg;base64,'],
			[QRCode::OUTPUT_MARKUP_SVG, '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'],
			[QRCode::OUTPUT_MARKUP_HTML, '<div><span style="background:'],
			[QRCode::OUTPUT_STRING_TEXT, '⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕⭕'.PHP_EOL],
			[QRCode::OUTPUT_STRING_JSON, '[[18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18],'],
		];
	}

	/**
	 * @dataProvider typeDataProvider
	 * @param $type
	 */
	public function testRenderImage($type, $expected){
		$this->qrcode = $this->reflection->newInstanceArgs([new QROptions(['outputType' => $type])]);

		$this->assertContains($expected, $this->qrcode->render('test'));
	}

	/**
	 * @expectedException \chillerlan\QRCode\QRCodeException
	 * @expectedExceptionMessage Invalid error correct level: 42
	 */
	public function testSetOptionsException(){
		$this->qrcode->setOptions(new QROptions(['eccLevel' => 42]));
	}

	/**
	 * @expectedException \chillerlan\QRCode\Output\QRCodeOutputException
	 * @expectedExceptionMessage invalid output type
	 */
	public function testInitDataInterfaceException(){
		$this->qrcode->setOptions(new QROptions(['outputType' => 'foo']))->render('test');
	}

	/**
	 * @expectedException \chillerlan\QRCode\Data\QRCodeDataException
	 * @expectedExceptionMessage QRCode::getMatrix() No data given.
	 */
	public function testGetMatrixException(){
		$this->qrcode->getMatrix('');
	}

}
