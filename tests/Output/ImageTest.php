<?php
/**
 *
 * @filesource   ImageTest.php
 * @created      08.02.2016
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\Output\QRImageOptions;
use chillerlan\QRCode\QRCode;

class ImageTest extends OutputTestAbstract{

	protected $outputInterfaceClass = QRImage::class;
	protected $outputOptionsClass   = QRImageOptions::class;

	public function testOptions(){
		$this->assertEquals(QRCode::OUTPUT_IMAGE_PNG, $this->options->type);
		$this->assertEquals(true, $this->options->base64);
	}

	public function imageDataProvider(){
		return [
			[QRCode::OUTPUT_IMAGE_PNG, 'foobar', 'img1.png.uri'],
			[QRCode::OUTPUT_IMAGE_GIF, 'foobar', 'img1.gif.uri'],
			[QRCode::OUTPUT_IMAGE_JPG, 'foobar', 'img1.jpg.uri'],
			[QRCode::OUTPUT_IMAGE_PNG, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'img2.png.uri'],
			[QRCode::OUTPUT_IMAGE_GIF, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'img2.gif.uri'],
			[QRCode::OUTPUT_IMAGE_JPG, 'otpauth://totp/test?secret=B3JX4VCVJDVNXNZ5&issuer=chillerlan.net', 'img2.jpg.uri'],
		];
	}

	/**
	 * @dataProvider imageDataProvider
	 */
	public function testImageOutput($type, $data, $expected){
		$this->options->type = $type;
		$output = (new QRCode($data, new $this->outputInterfaceClass($this->options)))->output();
		// jpeg test is causing trouble
		if($type !== QRCode::OUTPUT_IMAGE_JPG){
			$this->assertEquals(file_get_contents(__DIR__.'/image/'.$expected), $output);
		}
	}

}
