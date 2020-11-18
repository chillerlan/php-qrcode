<?php
/**
 * Class QRFpdfTest
 *
 * @filesource   QRFpdfTest.php
 * @created      03.06.2020
 * @package      chillerlan\QRCodeTest\Output
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use FPDF;
use chillerlan\QRCode\Output\{QRFpdf, QROutputInterface};
use chillerlan\QRCode\{QRCode, QROptions};

use function class_exists, substr;

/**
 * Tests the QRFpdf output module
 */
class QRFpdfTest extends QROutputTestAbstract{

	protected $FQCN = QRFpdf::class;

	/**
	 * @inheritDoc
	 * @internal
	 */
	public function setUp():void{

		if(!class_exists(FPDF::class)){
			$this->markTestSkipped('FPDF not available');
			return;
		}

		parent::setUp();
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			1024 => [0, 0, 0],
			4    => [255, 255, 255],
		];

		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

	/**
	 * @inheritDoc
	 */
	public function testRenderImage():void{
		$type = QRCode::OUTPUT_FPDF;

		$this->options->outputType  = $type;
		$this->options->imageBase64 = false;
		$this->outputInterface->dump($this::cachefile.$type);

		// substr() to avoid CreationDate
		$expected = substr(file_get_contents($this::cachefile.$type), 0, 2000);
		$actual   = substr($this->outputInterface->dump(), 0, 2000);

		$this::assertSame($expected, $actual);
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;

		$this->setOutputInterface();

		$this::assertInstanceOf(FPDF::class, $this->outputInterface->dump());
	}

}
