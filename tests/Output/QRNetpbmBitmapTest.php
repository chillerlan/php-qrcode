<?php
/**
 * Class QRNetpbmBitmapTest
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
use chillerlan\QRCode\Output\{QROutputInterface, QRNetpbmBitmap};
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests the QRNetpbmBitmap output class
 */
final class QRNetpbmBitmapTest extends QROutputTestAbstract {

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRNetpbmBitmap($options, $matrix);
	}

	/**
	 * @phpstan-return array<string, array{0: mixed, 1: bool}>
	 */
	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type: array' => [[], false],
			'invalid: wrong type: string' => ['abc', false],
			'valid: true' => [true, true],
			'valid: false' => [false, true],
		];
	}

	#[Test]
	public function setModuleValues():void{
			$this->options->moduleValues = [
				// data
				QRMatrix::M_DATA_DARK => true,
				QRMatrix::M_DATA      => false,
			];

			$this->options->outputBase64 = false;
			$this->options->netpbmPlain = true;
			$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
			$data = $this->outputInterface->dump();

			$this::assertStringContainsString('1', $data);
			$this::assertStringContainsString('0', $data);
	}

	#[Test]
        public function renderToCacheFilePlain() {
		$this->options->netpbmPlain = true;
		$this->renderToCacheFile();
	}
}
