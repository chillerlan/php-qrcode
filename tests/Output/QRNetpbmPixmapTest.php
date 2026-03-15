<?php
/**
 * Class QRNetpbmPixmapTest
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
use chillerlan\QRCode\Output\{QROutputInterface, QRNetpbmPixmap};
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests the QRNetpbmPixmap output class
 */
final class QRNetpbmPixmapTest extends QROutputTestAbstract {

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRNetpbmPixmap($options, $matrix);
	}

	/**
	 * @phpstan-return array<string, array{0: mixed, 1: bool}>
	 */
	public static function moduleValueProvider():array{
		return [
			'invalid: wrong type: string' => ['abc', false],
			'invalid: wrong type: bool' => [true, false],
			'invalid: wrong type: integer' => [0, false],
			'invalid: wrong size: empty' => [[], false],
			'invalid: wrong size: 1' => [[1], false],
			'invalid: wrong size: too big' => [[1,2,3,4], false],
			'invalid: value out of bounds: negative' => [[-1,0,0], false],
			'invalid: value out of bounds: too big' => [[0,70000,0], false],
			'valid: dark' => [[0,0,0], true],
			'valid: colored' => [[0,150,3000], true],
			'valid: white' => [[255,255,255], true],
		];
	}

	#[Test]
	public function setModuleValues():void{
			$this->options->moduleValues = [
				// data
				QRMatrix::M_DATA_DARK => [11,12,13],
				QRMatrix::M_DATA      => [250,251,252],
			];

			$this->options->netpbmPlain = true;
			$this->options->outputBase64 = false;
			$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
			$data = $this->outputInterface->dump();

			$this::assertStringContainsString('11', $data);
			$this::assertStringContainsString('12', $data);
			$this::assertStringContainsString('13', $data);
			$this::assertStringContainsString('250', $data);
			$this::assertStringContainsString('251', $data);
			$this::assertStringContainsString('252', $data);
	}

        #[Test]
        public function renderToCacheFilePlain() {
                $this->options->netpbmPlain = true;
                $this->renderToCacheFile();
        }
}
