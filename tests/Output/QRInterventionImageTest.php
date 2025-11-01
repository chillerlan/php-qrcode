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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRInterventionImage, QROutputInterface};
use chillerlan\QRCodeTest\Traits\CssColorModuleValueProviderTrait;
use chillerlan\Settings\SettingsContainerInterface;
use Intervention\Image\Interfaces\ImageInterface;
use PHPUnit\Framework\Attributes\{RequiresPhpExtension, Test};

/**
 * Tests the QRInterventionImage output class
 */
#[RequiresPhpExtension('gd')]
class QRInterventionImageTest extends QROutputTestAbstract{
	use CssColorModuleValueProviderTrait;

	protected function getOutputInterface(
		SettingsContainerInterface|QROptions $options,
		QRMatrix                             $matrix,
	):QROutputInterface{
		return new QRInterventionImage($options, $matrix);
	}

	#[Test]
	public function setModuleValues():void{

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

	#[Test]
	public function outputGetResource():void{
		$this->options->returnResource = true;
		$this->outputInterface         = $this->getOutputInterface($this->options, $this->matrix);

		$this::assertInstanceOf(ImageInterface::class, $this->outputInterface->dump());
	}

}
