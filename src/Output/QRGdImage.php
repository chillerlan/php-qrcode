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

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use ErrorException, GdImage, Throwable;
use function extension_loaded, imagecolorallocate, imagecolortransparent,
	imagecreatetruecolor, imagedestroy, imagefilledellipse, imagefilledrectangle,
	imagescale, imagetypes, intdiv, intval, max, min, ob_end_clean, ob_get_contents, ob_start,
	restore_error_handler, set_error_handler, sprintf;
use const IMG_AVIF, IMG_BMP, IMG_GIF, IMG_JPG, IMG_PNG, IMG_WEBP;

/**
 * Converts the matrix into GD images, raw or base64 output (requires ext-gd)
 *
 * @see https://php.net/manual/book.image.php
 * @see https://github.com/chillerlan/php-qrcode/issues/223
 */
abstract class QRGdImage extends QROutputAbstract{
	use RGBArrayModuleValueTrait;

	/**
	 * The GD image resource
	 *
	 * @see imagecreatetruecolor()
	 */
	protected GdImage $image;

	/**
	 * The allocated background color
	 *
	 * @see \imagecolorallocate()
	 */
	protected int $background;

	/**
	 * Whether we're running in upscale mode (scale < 20)
	 *
	 * @see \chillerlan\QRCode\QROptions::$drawCircularModules
	 */
	protected bool $upscaled = false;

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(SettingsContainerInterface|QROptions $options, QRMatrix $matrix){
		$this->options = $options;
		$this->matrix  = $matrix;

		$this->checkGD();

		if($this->options->invertMatrix){
			$this->matrix->invert();
		}

		$this->copyVars();
		$this->setMatrixDimensions();
	}

	/**
	 * Checks whether GD is installed and if the given mode is supported
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 * @codeCoverageIgnore
	 */
	protected function checkGD():void{

		if(!extension_loaded('gd')){
			throw new QRCodeOutputException('ext-gd not loaded');
		}

		$modes = [
			QRGdImageAVIF::class => IMG_AVIF,
			QRGdImageBMP::class  => IMG_BMP,
			QRGdImageGIF::class  => IMG_GIF,
			QRGdImageJPEG::class => IMG_JPG,
			QRGdImagePNG::class  => IMG_PNG,
			QRGdImageWEBP::class => IMG_WEBP,
		];

		// likely using custom output/manual invocation
		if(!isset($modes[$this->options->outputInterface])){
			return;
		}

		$mode = $modes[$this->options->outputInterface];

		if((imagetypes() & $mode) !== $mode){
			throw new QRCodeOutputException(sprintf('output mode "%s" not supported', $this->options->outputInterface));
		}

	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function prepareModuleValue(mixed $value):int{
		$values = [];

		foreach(array_values($value) as $i => $val){

			if($i > 2){
				break;
			}

			$values[] = max(0, min(255, intval($val)));
		}

		/** @phan-suppress-next-line PhanParamTooFewInternalUnpack */
		$color = imagecolorallocate($this->image, ...$values);

		if($color === false){
			throw new QRCodeOutputException('could not set color: imagecolorallocate() error');
		}

		return $color;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):int{
		return $this->prepareModuleValue(($isDark) ? [0, 0, 0] : [255, 255, 255]);
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \ErrorException
	 */
	public function dump(string $file = null):string|GdImage{

		set_error_handler(function(int $errno, string $errstr):bool{
			throw new ErrorException($errstr, $errno);
		});

		$this->image = $this->createImage();
		// set module values after image creation because we need the GdImage instance
		$this->setModuleValues();
		$this->setBgColor();

		imagefilledrectangle($this->image, 0, 0, $this->length, $this->length, $this->background);

		$this->drawImage();

		if($this->upscaled){
			// scale down to the expected size
			$this->image    = imagescale($this->image, ($this->length / 10), ($this->length / 10));
			$this->upscaled = false;
			// Reset scaled and length values after rescaling image to prevent issues with subclasses that use the output from dump()
			$this->setMatrixDimensions();
		}

		// set transparency after scaling, otherwise it would be undone
		// @see https://www.php.net/manual/en/function.imagecolortransparent.php#77099
		$this->setTransparencyColor();

		if($this->options->returnResource){
			restore_error_handler();

			return $this->image;
		}

		$imageData = $this->dumpImage();

		$this->saveToFile($imageData, $file);

		if($this->options->outputBase64){
			$imageData = $this->toBase64DataURI($imageData);
		}

		restore_error_handler();

		return $imageData;
	}

	/**
	 * Creates a new GdImage resource and scales it if necessary
	 *
	 * we're scaling the image up in order to draw crisp round circles, otherwise they appear square-y on small scales
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/23
	 */
	protected function createImage():GdImage{

		if($this->drawCircularModules && $this->options->gdImageUseUpscale && $this->options->scale < 20){
			// increase the initial image size by 10
			$this->length   *= 10;
			$this->scale    *= 10;
			$this->upscaled  = true;
		}

		return imagecreatetruecolor($this->length, $this->length);
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

		$this->background = $this->prepareModuleValue([255, 255, 255]);
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

		imagecolortransparent($this->image, $transparencyColor);
	}

	/**
	 * Draws the QR image
	 */
	protected function drawImage():void{
		foreach($this->matrix->getMatrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->module($x, $y, $M_TYPE);
			}
		}
	}

	/**
	 * Creates a single QR pixel with the given settings
	 */
	protected function module(int $x, int $y, int $M_TYPE):void{

		if(!$this->drawLightModules && !$this->matrix->isDark($M_TYPE)){
			return;
		}

		$color = $this->getModuleValue($M_TYPE);

		if($this->drawCircularModules && !$this->matrix->checkTypeIn($x, $y, $this->keepAsSquare)){
			imagefilledellipse(
				$this->image,
				(($x * $this->scale) + intdiv($this->scale, 2)),
				(($y * $this->scale) + intdiv($this->scale, 2)),
				(int)($this->circleDiameter * $this->scale),
				(int)($this->circleDiameter * $this->scale),
				$color
			);

			return;
		}

		imagefilledrectangle(
			$this->image,
			($x * $this->scale),
			($y * $this->scale),
			(($x + 1) * $this->scale),
			(($y + 1) * $this->scale),
			$color
		);
	}

	/**
	 * Renders the image with the gdimage function for the desired output
	 *
	 * @see https://github.com/chillerlan/php-qrcode/issues/223
	 */
	abstract protected function renderImage():void;

	/**
	 * Creates the final image by calling the desired GD output function
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function dumpImage():string{
		$exception = null;
		$imageData = null;

		ob_start();

		try{
			$this->renderImage();

			$imageData = ob_get_contents();
			imagedestroy($this->image);
		}
		// not going to cover edge cases
		// @codeCoverageIgnoreStart
		catch(Throwable $e){
			$exception = $e;
		}
		// @codeCoverageIgnoreEnd

		ob_end_clean();

		// throw here in case an exception happened within the output buffer
		if($exception instanceof Throwable){
			throw new QRCodeOutputException($exception->getMessage());
		}

		return $imageData;
	}

}
