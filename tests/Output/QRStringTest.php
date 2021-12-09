<?php
/**
 * Class QRStringTest
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRString};
use chillerlan\QRCodeExamples\MyCustomOutput;

/**
 * Tests the QRString output module
 */
final class QRStringTest extends QROutputTestAbstract{

	/**
	 * @inheritDoc
	 */
	protected function getOutputInterface(QROptions $options):QROutputInterface{
		return new QRString($options, $this->matrix);
	}

	/**
	 * @inheritDoc
	 */
	public function types():array{
		return [
			'json' => [QRCode::OUTPUT_STRING_JSON],
			'text' => [QRCode::OUTPUT_STRING_TEXT],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA | QRMatrix::IS_DARK => 'A',
			QRMatrix::M_DATA                     => 'B',
		];

		$this->outputInterface = $this->getOutputInterface($this->options);
		$data                  = $this->outputInterface->dump();

		$this::assertStringContainsString('A', $data);
		$this::assertStringContainsString('B', $data);
	}

	/**
	 * covers the custom output functionality via an example
	 */
	public function testCustomOutput():void{
		$this->options->version         = 5;
		$this->options->eccLevel        = EccLevel::L;
		$this->options->outputType      = QRCode::OUTPUT_CUSTOM;
		$this->options->outputInterface = MyCustomOutput::class;

		$this::assertSame(
			file_get_contents(__DIR__.'/../samples/custom'),
			(new QRCode($this->options))->render('test')
		);
	}

}
