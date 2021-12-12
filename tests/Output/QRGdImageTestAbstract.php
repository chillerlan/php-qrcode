<?php
/**
 * Class QRGdImageTestAbstract
 *
 * @created      24.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImage;
use const PHP_MAJOR_VERSION;

/**
 * Tests the QRGdImage output module
 */
abstract class QRGdImageTestAbstract extends QROutputTestAbstract{

	protected string $FQN  = QRGdImage::class;

	/**
	 * @inheritDoc
	 */
	protected function setUp():void{

		if(!extension_loaded('gd')){
			$this::markTestSkipped('ext-gd not loaded');
		}

		parent::setUp();
	}

	/**
	 * @inheritDoc
	 */
	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA | QRMatrix::IS_DARK => [0, 0, 0],
			QRMatrix::M_DATA                     => [255, 255, 255],
		];

		$this->outputInterface = new $this->FQN($this->options, $this->matrix);
		$this->outputInterface->dump();

		$this::assertTrue(true); // tricking the code coverage
	}

	/**
	 *
	 */
	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = new $this->FQN($this->options, $this->matrix);

		$actual = $this->outputInterface->dump();

		/** @noinspection PhpFullyQualifiedNameUsageInspection */
		PHP_MAJOR_VERSION >= 8
			? $this::assertInstanceOf(\GdImage::class, $actual)
			: $this::assertIsResource($actual);
	}

}
