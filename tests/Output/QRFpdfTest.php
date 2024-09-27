<?php
/**
 * Class QRFpdfTest
 *
 * @created      03.06.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRFpdf, QROutputInterface};
use chillerlan\Settings\SettingsContainerInterface;
use FPDF;
use function class_exists;

/**
 * Tests the QRFpdf output module
 */
final class QRFpdfTest extends QROutputTestAbstract{
	use RGBArrayModuleValueProviderTrait;

	protected function setUp():void{

		if(!class_exists(FPDF::class)){
			$this::markTestSkipped('FPDF not available');
		}

		parent::setUp();
	}

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRFpdf($options, $matrix);
	}

	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => [0, 0, 0],
			QRMatrix::M_DATA      => [255, 255, 255],
		];

		$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
		$this->outputInterface->dump();

		/** @phpstan-ignore-next-line */
		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertInstanceOf(FPDF::class, $this->outputInterface->dump());
	}

}
