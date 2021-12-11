<?php
/**
 * Class QRImage
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
use chillerlan\QRCode\QRCode;
use chillerlan\Settings\SettingsContainerInterface;
use ErrorException, Exception;

use function array_values, count, extension_loaded, imagecolorallocate, imagecolortransparent, imagecreatetruecolor,
	 imagedestroy, imagefilledellipse, imagefilledrectangle, imagegif, imagejpeg, imagepng, imagescale, in_array,
	is_array, ob_end_clean, ob_get_contents, ob_start, range, restore_error_handler, set_error_handler;
use const IMG_BICUBIC;

/**
 * Converts the matrix into GD images, raw or base64 output (requires ext-gd)
 *
 * @see http://php.net/manual/book.image.php
 */
class QRImage extends QROutputAbstract{

	/**
	 * GD image types that support transparency
	 *
	 * @var string[]
	 */
	protected const TRANSPARENCY_TYPES = [
		QRCode::OUTPUT_IMAGE_PNG,
		QRCode::OUTPUT_IMAGE_GIF,
	];

	protected string $defaultMode = QRCode::OUTPUT_IMAGE_PNG;

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
		return is_array($value) && count($value) >= 3;
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):array{
		return array_values($value);
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

		// avoid: "Indirect modification of overloaded property $imageTransparencyBG has no effect"
		// https://stackoverflow.com/a/10455217
		$tbg        = $this->options->imageTransparencyBG;
		/** @phan-suppress-next-line PhanParamTooFewInternalUnpack */
		$background = imagecolorallocate($this->image, ...$tbg);

		if($this->options->imageTransparent && in_array($this->options->outputType, $this::TRANSPARENCY_TYPES, true)){
			imagecolortransparent($this->image, $background);
		}

		imagefilledrectangle($this->image, 0, 0, $this->length, $this->length, $background);

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->setPixel($x, $y, $M_TYPE);
			}
		}

		// scale down to the expected size
		if($this->options->drawCircularModules && $this->options->scale <= 20){
			$this->image = imagescale($this->image, $this->length/10, $this->length/10, IMG_BICUBIC);
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
			$imageData = $this->base64encode($imageData, 'image/'.$this->options->outputType);
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

		$this->options->drawCircularModules && !$this->matrix->checkTypes($x, $y, $this->options->keepAsSquare)
			? imagefilledellipse(
				$this->image,
				($x * $this->scale) + ($this->scale / 2),
				($y * $this->scale) + ($this->scale / 2),
				2 * $this->options->circleRadius * $this->scale,
				2 * $this->options->circleRadius * $this->scale,
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
			$this->{$this->outputMode ?? $this->defaultMode}();
		}
		// not going to cover edge cases
		// @codeCoverageIgnoreStart
		catch(Exception $e){
			throw new QRCodeOutputException($e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		$imageData = ob_get_contents();
		imagedestroy($this->image);

		ob_end_clean();

		return $imageData;
	}

	/**
	 * PNG output
	 *
	 * @return void
	 */
	protected function png():void{
		imagepng(
			$this->image,
			null,
			in_array($this->options->pngCompression, range(-1, 9), true)
				? $this->options->pngCompression
				: -1
		);
	}

	/**
	 * Jiff - like... JitHub!
	 *
	 * @return void
	 */
	protected function gif():void{
		imagegif($this->image);
	}

	/**
	 * JPG output
	 *
	 * @return void
	 */
	protected function jpg():void{
		imagejpeg(
			$this->image,
			null,
			in_array($this->options->jpegQuality, range(0, 100), true)
				? $this->options->jpegQuality
				: 85
		);
	}

}
