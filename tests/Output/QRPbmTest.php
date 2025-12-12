<?php
/**
 * Class QRPbmTest
 *
 * @created      11.12.2025
 * @author       wgevaert
 * @copyright    2025 wgevaert
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRPbm};
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests the QRPbm output class
 */
final class QRPbmTest extends QROutputTestAbstract{

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRPbm($options, $matrix);
	}

	/**
	 * @phpstan-return array<string, array{0: mixed, 1: bool}>
	 */
	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type' => [[], false],
			'invalid: Not 0 or 1' => ['abc', false],
			'invalid: empty string' => ['', false],
			'invalid: space string' => [' ', false],
                        'valid: zero' => ['0', true],
		];
	}

        #[Test]
	public function setModuleValues():void{

			$this->options->moduleValues = [
					// data
					QRMatrix::M_DATA_DARK => '1',
					QRMatrix::M_DATA      => '0',
			];

			$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
			$data                  = $this->outputInterface->dump();

			$this::assertStringContainsString('1', $data);
			$this::assertStringContainsString('0', $data);
	}
}
