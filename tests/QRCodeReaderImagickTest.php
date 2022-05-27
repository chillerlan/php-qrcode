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

use chillerlan\QRCode\Decoder\IMagickLuminanceSource;
use chillerlan\QRCode\QRCode;
use function extension_loaded;

/**
 * Tests the Imagick based reader
 */
final class QRCodeReaderImagickTest extends QRCodeReaderTestAbstract{

	protected string $FQN = IMagickLuminanceSource::class;

	protected function setUp():void{

		if(!extension_loaded('imagick')){
			$this::markTestSkipped('imagick not installed');
		}

		parent::setUp();

		$this->options->readerUseImagickIfAvailable = true;
	}

	public function vectorQRCodeProvider():array{
		return [
			'SVG' => ['vector_sample.svg', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', false],
			'EPS' => ['vector_sample.eps', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', false],
		];
	}

	/**
	 * @dataProvider vectorQRCodeProvider
	 */
	public function testReadVectorFormats(string $img, string $expected):void{
		$this::assertSame($expected, (string)(new QRCode)
			->readFromSource(IMagickLuminanceSource::fromFile(__DIR__.'/samples/'.$img, $this->options)));
	}

}
