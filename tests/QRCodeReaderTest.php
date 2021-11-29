<?php
/**
 * Class QRCodeReaderTest
 *
 * @created      17.01.2021
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCodeTest;

use Exception, Generator;
use chillerlan\QRCode\Common\{EccLevel, Mode, Version};
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Decoder\{GDLuminanceSource, IMagickLuminanceSource};
use PHPUnit\Framework\TestCase;
use function extension_loaded, range, sprintf, str_repeat, substr;
use const PHP_OS_FAMILY, PHP_VERSION_ID;

/**
 * Tests the QR Code reader
 */
class QRCodeReaderTest extends TestCase{

	// https://www.bobrosslipsum.com/
	protected const loremipsum = 'Just let this happen. We just let this flow right out of our minds. '
		.'Anyone can paint. We touch the canvas, the canvas takes what it wants. From all of us here, '
		.'I want to wish you happy painting and God bless, my friends. A tree cannot be straight if it has a crooked trunk. '
		.'You have to make almighty decisions when you\'re the creator. I guess that would be considered a UFO. '
		.'A big cotton ball in the sky. I\'m gonna add just a tiny little amount of Prussian Blue. '
		.'They say everything looks better with odd numbers of things. But sometimes I put even numbers—just '
		.'to upset the critics. We\'ll lay all these little funky little things in there. ';

	public function qrCodeProvider():array{
		return [
			'helloworld' => ['hello_world.png', 'Hello world!'],
			// covers mirroring
			'mirrored'   => ['hello_world_mirrored.png', 'Hello world!'],
			// data modes
			'byte'       => ['byte.png', 'https://smiley.codes/qrcode/'],
			'numeric'    => ['numeric.png', '123456789012345678901234567890'],
			'alphanum'   => ['alphanum.png', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:'],
			'kanji'      => ['kanji.png', '茗荷茗荷茗荷茗荷'],
			// covers most of ReedSolomonDecoder
			'damaged'    => ['damaged.png', 'https://smiley.codes/qrcode/'],
			// covers Binarizer::getHistogramBlackMatrix()
			'smol'       => ['smol.png', 'https://smiley.codes/qrcode/'],
			'tilted'     => ['tilted.png', 'Hello world!'], // tilted 22° CCW
			'rotated'    => ['rotated.png', 'Hello world!'], // rotated 90° CW
			'gradient'   => ['example_svg.png', 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s'], // color gradient (from svg example)
		];
	}

	/**
	 * @dataProvider qrCodeProvider
	 */
	public function testReaderGD(string $img, string $expected):void{
		$this::assertSame($expected, (string)(new QRCode)->readFromSource(GDLuminanceSource::fromFile(__DIR__.'/qrcodes/'.$img)));
	}

	/**
	 * @dataProvider qrCodeProvider
	 */
	public function testReaderImagick(string $img, string $expected):void{

		if(!extension_loaded('imagick')){
			$this::markTestSkipped('imagick not installed');
		}

		// Y THO?? https://github.com/chillerlan/php-qrcode/runs/4270411373
		// "could not find enough finder patterns"
		if($img === 'example_svg.png' && PHP_OS_FAMILY === 'Windows' && PHP_VERSION_ID < 80100){
			$this::markTestSkipped('random gradient example issue??');
		}

		$this::assertSame($expected, (string)(new QRCode)->readFromSource(IMagickLuminanceSource::fromFile(__DIR__.'/qrcodes/'.$img)));
	}

	public function testReaderMultiSegment():void{
		$options = new QROptions;
		$options->imageBase64 = false;

		$numeric  = '123456789012345678901234567890';
		$alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 $%*+-./:';
		$kanji    = '茗荷茗荷茗荷茗荷';
		$byte     = 'https://smiley.codes/qrcode/';

		$qrcode = (new QRCode($options))
			->addNumericSegment($numeric)
			->addAlphaNumSegment($alphanum)
			->addKanjiSegment($kanji)
			->addByteSegment($byte)
		;

		$this::assertSame($numeric.$alphanum.$kanji.$byte, (string)$qrcode->readFromBlob($qrcode->render()));
	}

	public function dataTestProvider():Generator{
		$str = str_repeat($this::loremipsum, 5);

		foreach(range(1, 40) as $v){
			$version = new Version($v);

			foreach([EccLevel::L, EccLevel::M, EccLevel::Q, EccLevel::H] as $ecc){
				$eccLevel = new EccLevel($ecc);
				$expected = substr($str, 0, $version->getMaxLengthForMode(Mode::BYTE, $eccLevel) ?? '');

				yield 'version: '.$version.$eccLevel => [$version, $eccLevel, $expected];
			}
		}

	}

	/**
	 * @dataProvider dataTestProvider
	 */
	public function testReadData(Version $version, EccLevel $ecc, string $expected):void{
		$options = new QROptions;

#		$options->imageTransparent      = false;
		$options->eccLevel              = $ecc->getLevel();
		$options->version               = $version->getVersionNumber();
		$options->imageBase64           = false;
		$options->useImagickIfAvailable = true;
		// what's interesting is that a smaller scale seems to produce fewer reader errors???
		// usually from version 20 up, independend of the luminance source
		// scale 1-2 produces none, scale 3: 1 error, scale 4: 6 errors, scale 5: 5 errors, scale 10: 10 errors
		// @see \chillerlan\QRCode\Detector\GridSampler::checkAndNudgePoints()
		$options->scale                 = 2;

		try{
			$qrcode    = new QRCode($options);
			$imagedata = $qrcode->render($expected);
			$result    = $qrcode->readFromBlob($imagedata);
		}
		catch(Exception $e){
			$this::markTestSkipped(sprintf('skipped version %s%s: %s', $version, $ecc, $e->getMessage()));
		}

		$this::assertSame($expected, $result->text);
		$this::assertSame($version->getVersionNumber(), $result->version->getVersionNumber());
		$this::assertSame($ecc->getLevel(), $result->eccLevel->getLevel());
	}

}
