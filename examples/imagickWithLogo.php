<?php
/**
 * @created      28.02.2023
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2023 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\{QRImagick, QROutputInterface};

require_once __DIR__.'/../vendor/autoload.php';


/*
 * Class definition
 */

class QRImagickWithLogo extends QRImagick{

	/**
	 * @inheritDoc
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(string $file = null):string{
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
		$this->imagick->compositeImage($imLogo, Imagick::COMPOSITE_ATOP, $pos, $pos);

		// output (retain functionality of the parent class)
		$imageData = $this->imagick->getImageBlob();

		$this->imagick->destroy();
		$this->saveToFile($imageData, $file);

		if($this->options->imageBase64){
			$imageData = $this->toBase64DataURI($imageData, (new finfo(FILEINFO_MIME_TYPE))->buffer($imageData));
		}

		return $imageData;
	}

}

/**
 * augment the QROptions class
 */
class ImagickWithLogoOptions extends QROptions{

	protected string $pngLogo;

	/**
	 * check logo
	 *
	 * of course, we could accept other formats too.
	 * we're not checking for the file type either for simplicity reasons (assuming PNG)
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

$options = new ImagickWithLogoOptions([
	'pngLogo'             => __DIR__.'/octocat.png', // setting from the augmented options
	'version'             => 5,
	'outputType'          => QROutputInterface::CUSTOM,
	'outputInterface'     => QRImagickWithLogo::class, // use the custom output class
	'eccLevel'            => EccLevel::H,
	'imageBase64'         => false,
	'addLogoSpace'        => true,
	'logoSpaceWidth'      => 15,
	'logoSpaceHeight'     => 15,
	'bgColor'             => '#eee',
	'imageTransparent'    => true,
	'scale'               => 20,
	'drawLightModules'    => false,
	'drawCircularModules' => true,
	'circleRadius'        => 0.4,
	'keepAsSquare'        => [
		QRMatrix::M_FINDER_DARK,
		QRMatrix::M_FINDER_DOT,
		QRMatrix::M_ALIGNMENT_DARK,
	],
]);


header('Content-type: image/png');

// dump the output, with an additional logo
echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

exit;
