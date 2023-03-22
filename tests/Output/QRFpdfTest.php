<?php
/**
 * Class QRFpdfTest
 *
 * @created      03.06.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use FPDF;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRFpdf, QROutputInterface};

use function class_exists;

/**
 * Tests the QRFpdf output module
 */
final class QRFpdfTest extends QROutputTestAbstract{

	protected string $FQN  = QRFpdf::class;
	protected string $type = QROutputInterface::FPDF;

	/**
	 * @inheritDoc
	 */
	protected function setUp():void{

		if(!class_exists(FPDF::class)){
			$this::markTestSkipped('FPDF not available');
		}

		parent::setUp();
	}

	public static function moduleValueProvider():array{
		return [
			'valid: int'                     => [[123, 123, 123], true],
			'valid: w/invalid extra element' => [[123, 123, 123, 'abc'], true],
			'valid: numeric string'          => [['123', '123', '123'], true],
			'invalid: wrong type'            => ['foo', false],
			'invalid: array too short'       => [[1, 2], false],
			'invalid: contains non-number'   => [[1, 'b', 3], false],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => [0, 0, 0],
			QRMatrix::M_DATA      => [255, 255, 255],
		];

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = new $this->FQN($this->options, $this->matrix);

		$this::assertInstanceOf(FPDF::class, $this->outputInterface->dump());
	}

}
