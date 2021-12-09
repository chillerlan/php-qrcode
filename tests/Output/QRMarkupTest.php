<?php
/**
 * Class QRMarkupTest
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRMarkup};

/**
 * Tests the QRMarkup output module
 */
final class QRMarkupTest extends QROutputTestAbstract{

	/**
	 * @inheritDoc
	 */
	protected function getOutputInterface(QROptions $options):QROutputInterface{
		return new QRMarkup($options, $this->matrix);
	}

	/**
	 * @inheritDoc
	 */
	public function types():array{
		return [
			'html' => [QRCode::OUTPUT_MARKUP_HTML],
			'svg'  => [QRCode::OUTPUT_MARKUP_SVG],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{
		$this->options->imageBase64      = false;
		$this->options->imageTransparent = false;
		$this->options->moduleValues     = [
			// data
			QRMatrix::M_DATA | QRMatrix::IS_DARK => '#4A6000',
			QRMatrix::M_DATA                     => '#ECF9BE',
		];

		$this->outputInterface = $this->getOutputInterface($this->options);
		$data = $this->outputInterface->dump();
		$this::assertStringContainsString('#4A6000', $data);
		$this::assertStringContainsString('#ECF9BE', $data);
	}

}
