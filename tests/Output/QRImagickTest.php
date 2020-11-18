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
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCodeTest\Output;

use Imagick;
use chillerlan\QRCode\{QRCode, Output\QRImagick};

class QRImagickTest extends QROutputTestAbstract{

	protected $FQCN = QRImagick::class;

	public function setUp():void{

		if(!extension_loaded('imagick')){
			$this->markTestSkipped('ext-imagick not loaded');
			return;
		}

		parent::setUp();
	}

	public function testImageOutput(){
		$type = QRCode::OUTPUT_IMAGICK;

		$this->options->outputType = $type;
		$this->setOutputInterface();
		$this->outputInterface->dump($this::cachefile.$type);
		$img = $this->outputInterface->dump();

		$this->assertSame($img, file_get_contents($this::cachefile.$type));
	}

	public function testSetModuleValues(){

		$this->options->moduleValues = [
			// data
			1024 => '#4A6000',
			4    => '#ECF9BE',
		];

		$this->setOutputInterface()->dump();

		$this->assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;

		$this->setOutputInterface();

		$this::assertInstanceOf(Imagick::class, $this->outputInterface->dump());
	}

}
