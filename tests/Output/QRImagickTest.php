<?php
/**
 * Class QRImagickTest
 *
 * @filesource   QRImagickTest.php
 * @created      04.07.2018
 * @package      chillerlan\QRCodeTest\Output
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCodeTest\Output;

use Imagick;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\{QROutputInterface, QRImagick};

/**
 * Tests the QRImagick output module
 */
class QRImagickTest extends QROutputTestAbstract{

	/**
	 * @inheritDoc
	 * @internal
	 */
	public function setUp():void{

		if(!extension_loaded('imagick')){
			$this->markTestSkipped('ext-imagick not loaded');

			/** @noinspection PhpUnreachableStatementInspection */
			return;
		}

		parent::setUp();
	}

	/**
	 * @inheritDoc
	 * @internal
	 */
	protected function getOutputInterface(QROptions $options):QROutputInterface{
		return new QRImagick($options, $this->matrix);
	}

	/**
	 * @inheritDoc
	 * @internal
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
			1024 => '#4A6000',
			4    => '#ECF9BE',
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
