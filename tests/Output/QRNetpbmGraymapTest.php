<?php
/**
 * Class QRNetpbmGraymapTest
 *
 * @created      24.12.2025
 * @author       wgevaert
 * @copyright    2025 wgevaert
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QROutputInterface, QRNetpbmGraymap};
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests the QRNetpbmGraymap output class
 */
final class QRNetpbmGraymapTest extends QROutputTestAbstract {

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRNetpbmGraymap($options, $matrix);
	}

	/**
	 * @phpstan-return array<string, array{0: mixed, 1: bool}>
	 */
	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type: array' => [[], false],
			'invalid: wrong type: string' => ['abc', false],
			'invalid: wrong type: bool' => [true, false],
			'invalid: out of bounds: negative' => [-1, false],
			'invalid: out of bounds: too big' => [70000, false],
			'valid: dark' => [0, true],
			'valid: light' => [65000, true],
		];
	}

	#[Test]
	public function setModuleValues():void{
			$this->options->moduleValues = [
				// data
				QRMatrix::M_DATA_DARK => 33,
				QRMatrix::M_DATA      => 99,
			];

			$this->options->netpbmPlain = true;
			$this->options->outputBase64 = false;
			$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
			$data = $this->outputInterface->dump();

			$this::assertStringContainsString('33', $data);
			$this::assertStringContainsString('99', $data);
	}

        #[Test]
        public function renderToCacheFilePlain() {
                $this->options->netpbmPlain = true;
                $this->renderToCacheFile();
        }
}
