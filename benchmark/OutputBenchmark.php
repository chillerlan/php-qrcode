<?php
/**
 * Class OutputBenchmark
 *
 * @created      23.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use chillerlan\QRCode\Common\{EccLevel, Mode};
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Output\{
	QREps, QRFpdf, QRGdImageJPEG, QRGdImagePNG, QRGdImageWEBP, QRImagick, QRMarkupSVG, QRStringJSON
};
use PhpBench\Attributes\{BeforeMethods, Subject};

/**
 * Tests the performance of the built-in output classes
 */
#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions', 'initMatrix'])]
final class OutputBenchmark extends BenchmarkAbstract{

	protected const ECC_LEVELS  = [EccLevel::H];
	protected const DATAMODES   = [Mode::BYTE => Byte::class];

	public function initOptions():void{

		$options = [
			'version'             => $this->version->getVersionNumber(),
			'eccLevel'            => $this->eccLevel->getLevel(),
			'connectPaths'        => true,
			'drawLightModules'    => true,
			'drawCircularModules' => true,
			'gdImageUseUpscale'   => false, // set to false to allow proper comparison
		];

		$this->initQROptions($options);
	}

	#[Subject]
	public function QREps():void{
		(new QREps($this->options, $this->matrix))->dump();
	}

	#[Subject]
	public function QRFpdf():void{
		(new QRFpdf($this->options, $this->matrix))->dump();
	}

	/**
	 * for some reason imageavif() is extremely slow, ~50x slower than imagepng()
	 */
#	#[Subject]
#	public function QRGdImageAVIF():void{
#		(new QRGdImageAVIF($this->options, $this->matrix))->dump();
#	}

	#[Subject]
	public function QRGdImageJPEG():void{
		(new QRGdImageJPEG($this->options, $this->matrix))->dump();
	}

	#[Subject]
	public function QRGdImagePNG():void{
		(new QRGdImagePNG($this->options, $this->matrix))->dump();
	}

	#[Subject]
	public function QRGdImageWEBP():void{
		(new QRGdImageWEBP($this->options, $this->matrix))->dump();
	}

	#[Subject]
	public function QRImagick():void{
		(new QRImagick($this->options, $this->matrix))->dump();
	}

	#[Subject]
	public function QRMarkupSVG():void{
		(new QRMarkupSVG($this->options, $this->matrix))->dump();
	}

	#[Subject]
	public function QRStringJSON():void{
		(new QRStringJSON($this->options, $this->matrix))->dump();
	}

}
