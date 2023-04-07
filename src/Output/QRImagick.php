<?php
/**
 * Class QRImagick
 *
 * @created      04.07.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use finfo, Imagick, ImagickDraw, ImagickPixel;
use function extension_loaded, in_array, is_string, preg_match, strlen;
use const FILEINFO_MIME_TYPE;

/**
 * ImageMagick output module (requires ext-imagick)
 *
 * @see http://php.net/manual/book.imagick.php
 * @see http://phpimagick.com
 */
class QRImagick extends QROutputAbstract{

	/**
	 * The main image instance
	 */
	protected Imagick $imagick;

	/**
	 * The main draw instance
	 */
	protected ImagickDraw $imagickDraw;

	/**
	 * The allocated background color
	 */
	protected ImagickPixel $background;

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!extension_loaded('imagick')){
			throw new QRCodeOutputException('ext-imagick not loaded'); // @codeCoverageIgnore
		}

		if(!extension_loaded('fileinfo')){
			throw new QRCodeOutputException('ext-fileinfo not loaded'); // @codeCoverageIgnore
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * note: we're not necessarily validating the several values, just checking the general syntax
	 *
	 * @see https://www.php.net/manual/imagickpixel.construct.php
	 * @inheritDoc
	 */
	public static function moduleValueIsValid($value):bool{

		if(!is_string($value)){
			return false;
		}

		$value = trim($value);

		// hex notation
		// #rgb(a)
		// #rrggbb(aa)
		// #rrrrggggbbbb(aaaa)
		// ...
		if(preg_match('/^#[a-f]+$/i', $value) && in_array((strlen($value) - 1), [3, 4, 6, 8, 9, 12, 16, 24, 32], true)){
			return true;
		}

		// css (-like) func(...values)
		if(preg_match('#^(graya?|hs(b|la?)|rgba?)\([\d .,%]+\)$#i', $value)){
			return true;
		}

		// predefined css color
		if(preg_match('/^[a-z]+$/i', $value)){
			return true;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 * @throws \ImagickPixelException
	 */
	protected function prepareModuleValue($value):ImagickPixel{
		return new ImagickPixel($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):ImagickPixel{
		return $this->prepareModuleValue(($isDark) ? $this->options->markupDark : $this->options->markupLight);
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|\Imagick
	 */
	public function dump(string $file = null){
		$this->imagick = new Imagick;

		$this->setBgColor();

		$this->imagick->newImage($this->length, $this->length, $this->background, $this->options->imagickFormat);

		$this->drawImage();
		// set transparency color after all operations
		$this->setTransparencyColor();

		if($this->options->returnResource){
			return $this->imagick;
		}

		$imageData = $this->imagick->getImageBlob();

		$this->imagick->destroy();

		$this->saveToFile($imageData, $file);

		if($this->options->imageBase64){
			$imageData = $this->toBase64DataURI($imageData, (new finfo(FILEINFO_MIME_TYPE))->buffer($imageData));
		}

		return $imageData;
	}

	/**
	 * Sets the background color
	 */
	protected function setBgColor():void{

		if(isset($this->background)){
			return;
		}

		if($this::moduleValueIsValid($this->options->bgColor)){
			$this->background = $this->prepareModuleValue($this->options->bgColor);

			return;
		}

		$this->background = $this->prepareModuleValue('white');
	}

	/**
	 * Sets the transparency color
	 */
	protected function setTransparencyColor():void{

		if(!$this->options->imageTransparent){
			return;
		}

		$transparencyColor = $this->background;

		if($this::moduleValueIsValid($this->options->transparencyColor)){
			$transparencyColor = $this->prepareModuleValue($this->options->transparencyColor);
		}

		$this->imagick->transparentPaintImage($transparencyColor, 0.0, 10, false);
	}

	/**
	 * Creates the QR image via ImagickDraw
	 */
	protected function drawImage():void{
		$this->imagickDraw = new ImagickDraw;
		$this->imagickDraw->setStrokeWidth(0);

		for($y = 0; $y < $this->moduleCount; $y++){
			for($x = 0; $x < $this->moduleCount; $x++){
				$this->setPixel($x, $y);
			}
		}

		$this->imagick->drawImage($this->imagickDraw);
	}

	/**
	 * draws a single pixel at the given position
	 */
	protected function setPixel(int $x, int $y):void{

		if(!$this->options->drawLightModules && !$this->matrix->check($x, $y)){
			return;
		}

		$this->imagickDraw->setFillColor($this->getModuleValueAt($x, $y));

		$this->options->drawCircularModules && !$this->matrix->checkTypeIn($x, $y, $this->options->keepAsSquare)
			? $this->imagickDraw->circle(
				(($x + 0.5) * $this->scale),
				(($y + 0.5) * $this->scale),
				(($x + 0.5 + $this->options->circleRadius) * $this->scale),
				(($y + 0.5) * $this->scale)
			)
			: $this->imagickDraw->rectangle(
				($x * $this->scale),
				($y * $this->scale),
				(($x + 1) * $this->scale),
				(($y + 1) * $this->scale)
			);
	}

}
