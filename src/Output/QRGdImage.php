<?php
/**
 * Class QRGdImage
 *
 * @created      05.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use ErrorException, Throwable;
use function count, extension_loaded, imagecolorallocate, imagecolortransparent, imagecreatetruecolor,
	imagedestroy, imagefilledellipse, imagefilledrectangle, imagegif, imagejpeg, imagepng, imagescale, is_array, is_numeric,
	max, min, ob_end_clean, ob_get_contents, ob_start, restore_error_handler, set_error_handler;
use const IMG_BILINEAR_FIXED;

/**
 * Converts the matrix into GD images, raw or base64 output (requires ext-gd)
 *
 * @see http://php.net/manual/book.image.php
 */
class QRGdImage extends QROutputAbstract{

	/**
	 * The GD image resource
	 *
	 * @see imagecreatetruecolor()
	 * @var resource|\GdImage
	 */
	protected $image;

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!extension_loaded('gd')){
			throw new QRCodeOutputException('ext-gd not loaded'); // @codeCoverageIgnore
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{

		if(!is_array($value) || count($value) < 3){
			return false;
		}

		// check the first 3 values of the array
		for($i = 0; $i < 3; $i++){
			if(!is_numeric($value[$i])){
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):array{
		$v = [];

		for($i = 0; $i < 3; $i++){
			// clamp value
			$v[] = (int)max(0, min(255, $value[$i]));
		}

		return $v;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):array{
		return $isDark ? [0, 0, 0] : [255, 255, 255];
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|resource|\GdImage
	 *
	 * @phan-suppress PhanUndeclaredTypeReturnType, PhanTypeMismatchReturn
	 * @throws \ErrorException
	 */
	public function dump(string $file = null){

		/** @phan-suppress-next-line PhanTypeMismatchArgumentInternal */
		set_error_handler(function(int $severity, string $msg, string $file, int $line):void{
			throw new ErrorException($msg, 0, $severity, $file, $line);
		});

		$file ??= $this->options->cachefile;

		// we're scaling the image up in order to draw crisp round circles, otherwise they appear square-y on small scales
		if($this->options->drawCircularModules && $this->options->scale <= 20){
			$this->length  = ($this->length + 2) * 10;
			$this->scale  *= 10;
		}

		$this->image = imagecreatetruecolor($this->length, $this->length);

		// avoid: "Indirect modification of overloaded property $x has no effect"
		// https://stackoverflow.com/a/10455217
		$bgColor = $this->options->imageTransparencyBG;

		if($this->moduleValueIsValid($this->options->bgColor)){
			$bgColor = $this->getModuleValue($this->options->bgColor);
		}

		/** @phan-suppress-next-line PhanParamTooFewInternalUnpack */
		$background = imagecolorallocate($this->image, ...$bgColor);

		if(
			   $this->options->imageTransparent
			&& $this->options->outputType !== QROutputInterface::GDIMAGE_JPG
			&& $this->moduleValueIsValid($this->options->imageTransparencyBG)
		){
			$tbg = $this->getModuleValue($this->options->imageTransparencyBG);
			/** @phan-suppress-next-line PhanParamTooFewInternalUnpack */
			imagecolortransparent($this->image, imagecolorallocate($this->image, ...$tbg));
		}

		imagefilledrectangle($this->image, 0, 0, $this->length, $this->length, $background);

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->setPixel($x, $y, $M_TYPE);
			}
		}

		// scale down to the expected size
		if($this->options->drawCircularModules && $this->options->scale <= 20){
			$this->image = imagescale($this->image, $this->length/10, $this->length/10, IMG_BILINEAR_FIXED);
		}

		if($this->options->returnResource){
			restore_error_handler();

			return $this->image;
		}

		$imageData = $this->dumpImage();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		if($this->options->imageBase64){
			$imageData = $this->toBase64DataURI($imageData, 'image/'.$this->options->outputType);
		}

		restore_error_handler();

		return $imageData;
	}

	/**
	 * Creates a single QR pixel with the given settings
	 */
	protected function setPixel(int $x, int $y, int $M_TYPE):void{
		/** @phan-suppress-next-line PhanParamTooFewInternalUnpack */
		$color = imagecolorallocate($this->image, ...$this->moduleValues[$M_TYPE]);

		$this->options->drawCircularModules && $this->matrix->checkTypeNotIn($x, $y, $this->options->keepAsSquare)
			? imagefilledellipse(
				$this->image,
				(int)(($x * $this->scale) + ($this->scale / 2)),
				(int)(($y * $this->scale) + ($this->scale / 2)),
				(int)(2 * $this->options->circleRadius * $this->scale),
				(int)(2 * $this->options->circleRadius * $this->scale),
				$color
			)
			: imagefilledrectangle(
				$this->image,
				$x * $this->scale,
				$y * $this->scale,
				($x + 1) * $this->scale,
				($y + 1) * $this->scale,
				$color
			);
	}

	/**
	 * Creates the final image by calling the desired GD output function
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function dumpImage():string{
		ob_start();

		try{

			switch($this->options->outputType){
				case QROutputInterface::GDIMAGE_GIF:
					imagegif($this->image);
					break;
				case QROutputInterface::GDIMAGE_JPG:
					imagejpeg($this->image, null, max(0, min(100, $this->options->jpegQuality)));
					break;
				// silently default to png output
				case QROutputInterface::GDIMAGE_PNG:
				default:
					imagepng($this->image, null, max(-1, min(9, $this->options->pngCompression)));
			}

		}
		// not going to cover edge cases
		// @codeCoverageIgnoreStart
		catch(Throwable $e){
			throw new QRCodeOutputException($e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		$imageData = ob_get_contents();
		imagedestroy($this->image);

		ob_end_clean();

		return $imageData;
	}

}
