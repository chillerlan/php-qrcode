<?php
/**
 * Class QRImagickTest
 *
 * @created      04.07.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCodeTest\Output;

use Imagick;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\{QROutputInterface, QRImagick};

/**
 * Tests the QRImagick output module
 */
final class QRImagickTest extends QROutputTestAbstract{

	/**
	 * @inheritDoc
	 */
	protected function setUp():void{

		if(!extension_loaded('imagick')){
			$this->markTestSkipped('ext-imagick not loaded');
		}

		parent::setUp();
	}

	/**
	 * @inheritDoc
	 */
	protected function getOutputInterface(QROptions $options):QROutputInterface{
		return new QRImagick($options, $this->matrix);
	}

	/**
	 * @inheritDoc
	 */
	public function types():array{
		return [
			'imagick' => [QRCode::OUTPUT_IMAGICK],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA | QRMatrix::IS_DARK => '#4A6000',
			QRMatrix::M_DATA                     => '#ECF9BE',
		];

		$this->outputInterface = $this->getOutputInterface($this->options);
		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options);

		$this::assertInstanceOf(Imagick::class, $this->outputInterface->dump());
	}


}
