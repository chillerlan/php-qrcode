<?php
/**
 * Class QRCodeReaderImagickTest
 *
 * @created      12.12.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\{IMagickLuminanceSource, LuminanceSourceInterface};
use chillerlan\QRCode\Decoder\Decoder;
use chillerlan\Settings\SettingsContainerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use function extension_loaded;
use const PHP_OS_FAMILY;

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

	protected function getLuminanceSourceFromFile(
		string                               $file,
		SettingsContainerInterface|QROptions $options,
	):LuminanceSourceInterface{
		return IMagickLuminanceSource::fromFile($file, $options);
	}

	/**
	 * @phpstan-return array<string, array{0: string, 1: string}>
	 */
	public static function vectorQRCodeProvider():array{
		return [
			// SVG convert only works on windows (Warning: Option --export-png= is deprecated)
			'SVG' => ['vector_sample.svg', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
			// fails on linux because of security policy: https://stackoverflow.com/a/59193253
			// fails on windows with a FailedToExecuteCommand and file not found error
#			'EPS' => ['vector_sample.eps', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
		];
	}

	#[DataProvider('vectorQRCodeProvider')]
	public function testReadVectorFormats(string $img, string $expected):void{

		if(PHP_OS_FAMILY === 'Linux'){
			$this::markTestSkipped('avoid imagick conversion errors (ha ha)');
		}

		$luminanceSource = $this->getLuminanceSourceFromFile($this::samplesDir.$img, $this->options);

		$this::assertSame($expected, (string)(new Decoder)->decode($luminanceSource));
	}

}
