<?php
/**
 * Class QRMarkupTestAbstract
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;

/**
 * Tests the QRMarkup output module
 */
abstract class QRMarkupTestAbstract extends QROutputTestAbstract{
	use CssColorModuleValueProviderTrait;

	public function testSetModuleValues():void{
		$this->options->outputBase64     = false;
		$this->options->drawLightModules = true;
		$this->options->moduleValues     = [
			// data
			QRMatrix::M_DATA_DARK => '#4A6000',
			QRMatrix::M_DATA      => '#ECF9BE',
		];

		$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
		$data = $this->outputInterface->dump();
		$this::assertStringContainsString('#4A6000', $data);
		$this::assertStringContainsString('#ECF9BE', $data);
	}

}
