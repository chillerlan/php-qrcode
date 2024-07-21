<?php
/**
 * ImageMagick with logo output example
 *
 * @created      28.02.2023
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2023 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRImagick;

require_once __DIR__.'/../vendor/autoload.php';


/*
 * Class definition
 */

class QRImagickWithLogo extends QRImagick{

	/**
	 * @inheritDoc
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(string|null $file = null):string{
		// set returnResource to true to skip further processing for now
		$this->options->returnResource = true;

		// there's no need to save the result of dump() into $this->imagick here
		parent::dump($file);

		// set new logo size, leave a border of 1 module (no proportional resize/centering)
		$size = (($this->options->logoSpaceWidth - 2) * $this->options->scale);

		// logo position: the top left corner of the logo space
		$pos  = (($this->moduleCount * $this->options->scale - $size) / 2);

		// invoke logo instance
		$imLogo = new Imagick($this->options->pngLogo);
		$imLogo->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 0.85, true);

		// add the logo to the qrcode
		$this->imagick->compositeImage($imLogo, Imagick::COMPOSITE_BLEND, $pos, $pos);

		// output (retain functionality of the parent class)
		$imageData = $this->imagick->getImageBlob();

		$this->imagick->destroy();
		$this->saveToFile($imageData, $file);

		if($this->options->outputBase64){
			$imageData = $this->toBase64DataURI($imageData);
		}

		return $imageData;
	}

}

/**
 * augment the QROptions class
 *
 * @property string $pngLogo
 */
class ImagickWithLogoOptions extends QROptions{

	protected string $pngLogo;

	/**
	 * check logo
	 *
	 * of course, we could accept other formats too.
	 * we're not checking for the file type either for simplicity reasons (assuming PNG)
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_pngLogo(string $pngLogo):void{

		if(!file_exists($pngLogo) || !is_file($pngLogo) || !is_readable($pngLogo)){
			throw new QRCodeException('invalid png logo');
		}

		// @todo: validate png

		$this->pngLogo = $pngLogo;
	}

}


/*
 * Runtime
 */

$options = new ImagickWithLogoOptions;

$options->pngLogo             = __DIR__.'/octocat.png'; // setting from the augmented options
$options->version             = 5;
$options->outputInterface     = QRImagickWithLogo::class; // use the custom output class
$options->eccLevel            = EccLevel::H;
$options->outputBase64        = false;
$options->addLogoSpace        = true;
$options->logoSpaceWidth      = 15;
$options->logoSpaceHeight     = 15;
$options->bgColor             = '#eee';
$options->imageTransparent    = true;
$options->scale               = 20;
$options->drawLightModules    = false;
$options->drawCircularModules = true;
$options->circleRadius        = 0.4;
$options->keepAsSquare        = [
	QRMatrix::M_FINDER_DARK,
	QRMatrix::M_FINDER_DOT,
	QRMatrix::M_ALIGNMENT_DARK,
];


// dump the output, with an additional logo
$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');


header('Content-type: image/png');

echo $out;

exit;
