<?php
/**
 * Class QRCodeReaderImagickTest
 *
 * @created      12.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\{IMagickLuminanceSource, LuminanceSourceInterface};
use chillerlan\QRCode\Decoder\Decoder;
use function extension_loaded;
use const PHP_OS_FAMILY, PHP_VERSION_ID;

/**
 * Tests the Imagick based reader
 */
final class QRCodeReaderImagickTest extends QRCodeReaderTestAbstract{

	protected function setUp():void{

		if(!extension_loaded('imagick')){
			$this::markTestSkipped('imagick not installed');
		}

		parent::setUp();

		$this->options->readerUseImagickIfAvailable = true;
	}

	protected function getLuminanceSourceFromFile(string $file, QROptions $options):LuminanceSourceInterface{
		return IMagickLuminanceSource::fromFile($file, $options);
	}

	public static function vectorQRCodeProvider():array{
		return [
			// SVG convert only works on windows (Warning: Option --export-png= is deprecated)
			'SVG' => ['vector_sample.svg', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
			// fails on linux because of security policy: https://stackoverflow.com/a/59193253
			// fails on windows with a FailedToExecuteCommand and file not found error
#			'EPS' => ['vector_sample.eps', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
		];
	}

	/**
	 * @dataProvider vectorQRCodeProvider
	 */
	public function testReadVectorFormats(string $img, string $expected):void{

		if(PHP_OS_FAMILY === 'Linux'){
			$this::markTestSkipped('avoid imagick conversion errors (ha ha)');
		}

		if(PHP_OS_FAMILY === 'Windows' && PHP_VERSION_ID < 80100){
			$this::markTestSkipped('This test fails on Windows and PHP < 8.1 for whatever reason');
		}

		$luminanceSource = $this->getLuminanceSourceFromFile($this::samplesDir.$img, $this->options);

		$this::assertSame($expected, (string)(new Decoder)->decode($luminanceSource));
	}

}
