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
use GdImage;
use function extension_loaded;

/**
 * Tests the QRGdImage output module
 */
abstract class QRGdImageTestAbstract extends QROutputTestAbstract{
	use RGBArrayModuleValueProviderTrait;

	protected function setUp():void{

		if(!extension_loaded('gd')){
			$this::markTestSkipped('ext-gd not loaded');
		}

		parent::setUp();
	}

	public function testSetModuleValues():void{

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

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertInstanceOf(GdImage::class, $this->outputInterface->dump());
	}

	public function testBase64MimeType():void{
		$this->options->outputBase64 = true;
		$this->outputInterface       = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertStringContainsString($this->outputInterface::MIME_TYPE, $this->outputInterface->dump());
	}

}
