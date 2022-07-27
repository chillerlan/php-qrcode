<?php
/**
 * @created      18.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection, PhpIllegalPsrClassPathInspection
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRGdImage, QRCodeOutputException};

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Class definition
 */

class QRImageWithLogo extends QRGdImage{

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
			$imageData = $this->toBase64DataURI($imageData, 'image/'.$this->options->outputType);
		}

		return $imageData;
	}

}

/*
 * Runtime
 */

$options = new QROptions([
	'version'             => 5,
	'eccLevel'            => EccLevel::H,
	'imageBase64'         => false,
	'addLogoSpace'        => true,
	'logoSpaceWidth'      => 13,
	'logoSpaceHeight'     => 13,
	'scale'               => 6,
	'imageTransparent'    => false,
	'drawCircularModules' => true,
	'circleRadius'        => 0.45,
	'keepAsSquare'        => [QRMatrix::M_FINDER, QRMatrix::M_FINDER_DOT],
]);

$qrcode = new QRCode($options);
$qrcode->addByteSegment('https://github.com');

header('Content-type: image/png');

$qrOutputInterface = new QRImageWithLogo($options, $qrcode->getMatrix());

// dump the output, with an additional logo
// the logo could also be supplied via the options, see the svgWithLogo example
echo $qrOutputInterface->dump(null, __DIR__.'/octocat.png');

exit;
