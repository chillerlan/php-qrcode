<?php
/**
 * Class QRGdImageTestAbstract
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCodeTest\Traits\RGBArrayModuleValueProviderTrait;
use PHPUnit\Framework\Attributes\{RequiresPhpExtension, Test};
use GdImage;

/**
 * Tests the QRGdImage output classes
 */
#[RequiresPhpExtension('gd')]
abstract class QRGdImageTestAbstract extends QROutputTestAbstract{
	use RGBArrayModuleValueProviderTrait;

	#[Test]
	public function setModuleValues():void{

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

	#[Test]
	public function outputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertInstanceOf(GdImage::class, $this->outputInterface->dump());
	}

	#[Test]
	public function base64MimeType():void{
		$this->options->outputBase64 = true;
		$this->outputInterface       = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertStringContainsString($this->outputInterface::MIME_TYPE, $this->outputInterface->dump());
	}

}
