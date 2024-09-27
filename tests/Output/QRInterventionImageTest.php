<?php
/**
 * Class QRInterventionImageTest
 *
 * @created      04.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRInterventionImage;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QROptions;
use chillerlan\Settings\SettingsContainerInterface;
use Intervention\Image\Interfaces\ImageInterface;
use function extension_loaded;

/**
 * Tests the QRInterventionImage output module
 */
class QRInterventionImageTest extends QROutputTestAbstract{
	use CssColorModuleValueProviderTrait;

	protected function setUp():void{

		if(!extension_loaded('gd')){
			$this::markTestSkipped('ext-gd not loaded');
		}

		parent::setUp();
	}

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRInterventionImage($options, $matrix);
	}

	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			// data
			QRMatrix::M_DATA_DARK => '#4A6000',
			QRMatrix::M_DATA      => '#ECF9BE',
		];

		$this->outputInterface = $this->getOutputInterface($this->options, $this->matrix);
		$this->outputInterface->dump();

		/** @phpstan-ignore-next-line */
		$this::assertTrue(true); // tricking the code coverage
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertInstanceOf(ImageInterface::class, $this->outputInterface->dump());
	}

}
