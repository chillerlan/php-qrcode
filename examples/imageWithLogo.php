<?php
/**
 * @created      18.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\{QRCodeOutputException, QRImage};

require_once __DIR__.'/../vendor/autoload.php';

class QRImageWithLogo extends QRImage{

	/**
	 * @param string|null $file
	 * @param string|null $logo
	 *
	 * @return string
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(string $file = null, string $logo = null):string{
		// set returnResource to true to skip further processing for now
		$this->options->returnResource = true;

		// of course you could accept other formats too (such as resource or Imagick)
		// i'm not checking for the file type either for simplicity reasons (assuming PNG)
		if(!is_file($logo) || !is_readable($logo)){
			throw new QRCodeOutputException('invalid logo');
		}

		$this->matrix->setLogoSpace(
			$this->options->logoSpaceWidth,
			$this->options->logoSpaceHeight
		// not utilizing the position here
		);

		// there's no need to save the result of dump() into $this->image here
		parent::dump($file);

		$im = imagecreatefrompng($logo);

		// get logo image size
		$w = imagesx($im);
		$h = imagesy($im);

		// set new logo size, leave a border of 1 module (no proportional resize/centering)
		$lw = ($this->options->logoSpaceWidth - 2) * $this->options->scale;
		$lh = ($this->options->logoSpaceHeight - 2) * $this->options->scale;

		// get the qrcode size
		$ql = $this->matrix->size() * $this->options->scale;

		// scale the logo and copy it over. done!
		imagecopyresampled($this->image, $im, ($ql - $lw) / 2, ($ql - $lh) / 2, 0, 0, $lw, $lh, $w, $h);

		$imageData = $this->dumpImage();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		if($this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

}


/**
 * @property int $logoSpaceWidth
 * @property int $logoSpaceHeight
 */
class LogoOptions extends QROptions{
	// size in QR modules, multiply with QROptions::$scale for pixel size
	protected int $logoSpaceWidth;
	protected int $logoSpaceHeight;
}


$options = new LogoOptions;

$options->version          = 7;
$options->eccLevel         = QRCode::ECC_H;
$options->imageBase64      = false;
$options->logoSpaceWidth   = 13;
$options->logoSpaceHeight  = 13;
$options->scale            = 5;
$options->imageTransparent = false;


header('Content-type: image/png');

$qrOutputInterface = new QRImageWithLogo($options, (new QRCode($options))->getMatrix('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));

// dump the output, with an additional logo
echo $qrOutputInterface->dump(null, __DIR__.'/octocat.png');
