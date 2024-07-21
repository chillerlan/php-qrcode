<?php
/**
 * Class QRStringJSONTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRStringJSON};
use chillerlan\Settings\SettingsContainerInterface;

final class QRStringJSONTest extends QROutputTestAbstract{
	use CssColorModuleValueProviderTrait;

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRStringJSON($options, $matrix);
	}

	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => '#AAA',
			QRMatrix::M_DATA      => '#BBB',
		];

		$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
		$data                  = $this->outputInterface->dump();

		$this::assertStringContainsString('"layer":"data-dark","value":"#AAA"', $data);
		$this::assertStringContainsString('"layer":"data","value":"#BBB"', $data);
	}

}
