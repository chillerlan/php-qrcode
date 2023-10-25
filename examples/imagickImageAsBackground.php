<?php
/**
 * ImageMagick with image as background example
 *
 * @created      08.09.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRImagick;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\QROptions;

require_once __DIR__.'/../vendor/autoload.php';

class QRImagickImageAsBackground extends QRImagick{

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):ImagickPixel{
		// RGBA, adjust opacity to increase contrast
		return $this->prepareModuleValue(($isDark) ? '#00000040' : '#ffffffa0');
	}

	/**
	 * @inheritDoc
	 */
	protected function createImage():Imagick{
		$imagick = new Imagick($this->options->background);
		$width   = $imagick->getImageWidth();
		$height  = $imagick->getImageHeight();

		// crop the background if necessary
		if(($width / $height) !== 1){
			$cropsize = ($width > $height) ? $height : $width;

			$imagick->cropImage($cropsize, $cropsize, 0, 0);
		}

		// scale the background to fit the size of the QR Code
		if($imagick->getImageWidth() !== $this->length){
			$imagick->scaleImage($this->length, $this->length, true);
		}

		if($this->options->quality > -1){
			$imagick->setImageCompressionQuality(max(0, min(100, $this->options->quality)));
		}

		return $imagick;
	}

}


/**
 * augment the QROptions class
 */
class ImageAsBackgroundOptions extends QROptions{

	protected string $background;

	/**
	 * check background image
	 *
	 * @throws \chillerlan\QRCode\QRCodeException
	 */
	protected function set_background(string $background):void{

		if(!file_exists($background) || !is_file($background) || !is_readable($background)){
			throw new QRCodeException('invalid background file');
		}

		// @todo: check/validate desired image format

		$this->background = $background;
	}

}


/*
 * Runtime
 */

$options = new ImageAsBackgroundOptions;

$options->background           = __DIR__.'/background.jpg'; // setting from the augmented options
$options->version              = 5;
$options->outputType           = QROutputInterface::CUSTOM;
$options->outputInterface      = QRImagickImageAsBackground::class; // use the custom output class
$options->eccLevel             = EccLevel::H;
$options->outputBase64         = false;
$options->scale                = 10;
$options->drawLightModules     = true;
$options->svgUseFillAttributes = false;
$options->invertMatrix         = false;
$options->quietzoneSize        = 1;


// dump the output, with an additional logo
$out = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

if(PHP_SAPI !== 'cli'){
	header('Content-type: image/png');

	echo $out;
}

exit;
