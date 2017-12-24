<?php
/**
 * Class QRImageTest
 *
 * @filesource   QRImageTest.php
 * @created      24.12.2017
 * @package      chillerlan\QRCodeTest\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\Output\QRImage;
use chillerlan\QRCode\QRCode;

class QRImageTest extends QROutputTestAbstract{

	protected $FQCN = QRImage::class;

	public function types(){
		return [
			[QRCode::OUTPUT_IMAGE_PNG],
			[QRCode::OUTPUT_IMAGE_GIF],
			[QRCode::OUTPUT_IMAGE_JPG],
		];
	}

	/**
	 * @dataProvider types
	 * @param $type
	 */
	public function testImageOutput($type){
		$this->options->outputType = $type;
		$this->options->cachefile  = $this::cachefile.$type;
		$this->setOutputInterface();
		$this->outputInterface->dump();

		$this->options->cachefile = null;
		$this->options->imageBase64 = false;
		$this->setOutputInterface();
		$img = $this->outputInterface->dump();

		if($type === QRCode::OUTPUT_IMAGE_JPG){ // jpeg encoding may cause different results
			$this->markAsRisky();
		}

		$this->assertSame($img, file_get_contents($this::cachefile.$type));
	}

}
