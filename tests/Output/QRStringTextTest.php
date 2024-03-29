<?php
/**
 * Class QRStringTextTest
 *
 * @created      11.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRStringText};
use chillerlan\Settings\SettingsContainerInterface;

/**
 *
 */
final class QRStringTextTest extends QROutputTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix
	):QROutputInterface{
		return new QRStringText($options, $matrix);
	}

	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type'       => [[], false],
			'valid: string'             => ['abc', true],
			'valid: zero length string' => ['', true],
			'valid: empty string'       => [' ', true],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => 'A',
			QRMatrix::M_DATA      => 'B',
		];

		$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
		$data                  = $this->outputInterface->dump();

		$this::assertStringContainsString('A', $data);
		$this::assertStringContainsString('B', $data);
	}

}
