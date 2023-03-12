<?php
/**
 * example for additional text
 * @link https://github.com/chillerlan/php-qrcode/issues/35
 *
 * @created      22.06.2019
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2019 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QRImage;

require_once __DIR__.'/../vendor/autoload.php';

class QRImageWithText extends QRImage{

	/**
	 * @param string|null $file
	 * @param string|null $text
	 *
	 * @return string
	 */
	public function dump(string $file = null, string $text = null):string{
		// set returnResource to true to skip further processing for now
		$this->options->returnResource = true;

		// there's no need to save the result of dump() into $this->image here
		parent::dump($file);

		// render text output if a string is given
		if($text !== null){
			$this->addText($text);
		}

		$imageData = $this->dumpImage();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		if($this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

	/**
	 * @param string $text
	 */
	protected function addText(string $text):void{
		// save the qrcode image
		$qrcode = $this->image;

		// options things
		$textSize  = 3; // see imagefontheight() and imagefontwidth()
		$textBG    = [200, 200, 200];
		$textColor = [50, 50, 50];

		$bgWidth  = $this->length;
		$bgHeight = $bgWidth + 20; // 20px extra space

		// create a new image with additional space
		$this->image = imagecreatetruecolor($bgWidth, $bgHeight);
		$background  = imagecolorallocate($this->image, ...$textBG);

		// allow transparency
		if($this->options->imageTransparent && in_array($this->options->outputType, $this::TRANSPARENCY_TYPES, true)){
			imagecolortransparent($this->image, $background);
		}

		// fill the background
		imagefilledrectangle($this->image, 0, 0, $bgWidth, $bgHeight, $background);

		// copy over the qrcode
		imagecopymerge($this->image, $qrcode, 0, 0, 0, 0, $this->length, $this->length, 100);
		imagedestroy($qrcode);

		$fontColor = imagecolorallocate($this->image, ...$textColor);
		$w         = imagefontwidth($textSize);
		$x         = round(($bgWidth - strlen($text) * $w) / 2);

		// loop through the string and draw the letters
		foreach(str_split($text) as $i => $chr){
			imagechar($this->image, $textSize, (int)($i * $w + $x), $this->length, $chr, $fontColor);
		}
	}

}


$options = new QROptions([
	'version'     => 7,
	'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
	'scale'       => 3,
	'imageBase64' => false,
]);


header('Content-type: image/png');

$qrOutputInterface = new QRImageWithText($options, (new QRCode($options))->getMatrix('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));

// dump the output, with additional text
echo $qrOutputInterface->dump(null, 'example text');
