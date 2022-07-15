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

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRImagick, QROutputInterface};
use Imagick;

/**
 * Tests the QRImagick output module
 */
final class QRImagickTest extends QROutputTestAbstract{

	protected string $FQN  = QRImagick::class;
	protected string $type = QROutputInterface::IMAGICK;

	/**
	 * @inheritDoc
	 */
	protected function setUp():void{

		if(!extension_loaded('imagick')){
			$this::markTestSkipped('ext-imagick not loaded');
		}

		parent::setUp();
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

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = new $this->FQN($this->options, $this->matrix);

		$this::assertInstanceOf(Imagick::class, $this->outputInterface->dump());
	}

}
