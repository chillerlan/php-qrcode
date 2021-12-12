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

}
