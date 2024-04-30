<?php
/**
 * Class DecoderBenchmark
 *
 * @created      26.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeBenchmark;

use chillerlan\QRCode\Common\{GDLuminanceSource, IMagickLuminanceSource, Mode};
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Decoder\{Decoder, DecoderResult};
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCodeException;
use PhpBench\Attributes\{AfterMethods, BeforeMethods, Subject};
use RuntimeException;

/**
 * Tests the performance of the QR Code reader/decoder
 */
final class DecoderBenchmark extends BenchmarkAbstract{

	protected const DATAMODES   = [Mode::BYTE => Byte::class];

	private string        $imageBlob;
	private DecoderResult $result;

	public function initOptions():void{

		$options = [
			'version'          => $this->version->getVersionNumber(),
			'eccLevel'         => $this->eccLevel->getLevel(),
			'scale'            => 2,
			'imageTransparent' => false,
			'outputBase64'     => false,
		];

		$this->initQROptions($options);
	}

	public function generateImageBlob():void{
		$this->imageBlob = (new QRGdImagePNG($this->options, $this->matrix))->dump();
	}

	public function checkReaderResult():void{
		if($this->result->data !== $this->testData){
			throw new RuntimeException('invalid reader result');
		}
	}

	/**
	 * Tests the performance of the GD reader
	 */
	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions', 'initMatrix', 'generateImageBlob'])]
	#[AfterMethods(['checkReaderResult'])]
	public function GDLuminanceSource():void{

		// in rare cases the reader will be unable to detect and throw,
		// but we don't want the performance test to yell about it
		// @see QRCodeReaderTestAbstract::testReadData()
		try{
			$this->result = (new Decoder($this->options))
				->decode(GDLuminanceSource::fromBlob($this->imageBlob, $this->options));
		}
		catch(QRCodeException){
			// noop
		}
	}

	/**
	 * Tests the performance of the ImageMagick reader
	 */
	#[Subject]
	#[BeforeMethods(['assignParams', 'generateTestData', 'initOptions', 'initMatrix', 'generateImageBlob'])]
	#[AfterMethods(['checkReaderResult'])]
	public function IMagickLuminanceSource():void{
		$this->options->readerUseImagickIfAvailable = true;

		try{
			$this->result = (new Decoder($this->options))
				->decode(IMagickLuminanceSource::fromBlob($this->imageBlob, $this->options));
		}
		catch(QRCodeException){
			// noop
		}
	}

}
