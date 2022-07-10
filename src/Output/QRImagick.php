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
use Imagick, ImagickDraw, ImagickPixel;

use function extension_loaded, is_string;

/**
 * ImageMagick output module (requires ext-imagick)
 *
 * @see http://php.net/manual/book.imagick.php
 * @see http://phpimagick.com
 */
class QRImagick extends QROutputAbstract{

	protected Imagick $imagick;
	protected ImagickDraw $imagickDraw;

	/**
	 * @inheritDoc
	 *
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!extension_loaded('imagick')){
			throw new QRCodeOutputException('ext-imagick not loaded'); // @codeCoverageIgnore
		}

		parent::__construct($options, $matrix);
	}

	/**
	 * @inheritDoc
	 */
	protected function moduleValueIsValid($value):bool{
		return is_string($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getModuleValue($value):ImagickPixel{
		return new ImagickPixel($value);
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultModuleValue(bool $isDark):ImagickPixel{
		return new ImagickPixel($isDark ? $this->options->markupDark : $this->options->markupLight);
	}

	/**
	 * @inheritDoc
	 *
	 * @return string|\Imagick
	 */
	public function dump(string $file = null){
		$file          ??= $this->options->cachefile;
		$this->imagick = new Imagick;

		$this->imagick->newImage(
			$this->length,
			$this->length,
			new ImagickPixel($this->options->imagickBG ?? 'transparent'),
			$this->options->imagickFormat
		);

		$this->imagick->setImageType(Imagick::IMGTYPE_TRUECOLOR);

		$this->drawImage();

		if($this->options->returnResource){
			return $this->imagick;
		}

		$imageData = $this->imagick->getImageBlob();

		$this->imagick->destroy();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		return $imageData;
	}

	/**
	 * Creates the QR image via ImagickDraw
	 */
	protected function drawImage():void{
		$this->imagickDraw = new ImagickDraw;
		$this->imagickDraw->setStrokeWidth(0);

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->setPixel($x, $y, $M_TYPE);
			}
		}

		$this->imagick->drawImage($this->imagickDraw);
	}

	/**
	 * draws a single pixel at the given position
	 */
	protected function setPixel(int $x, int $y, int $M_TYPE):void{
		$this->imagickDraw->setFillColor($this->moduleValues[$M_TYPE]);

		$this->options->drawCircularModules && $this->matrix->checkTypeNotIn($x, $y, $this->options->keepAsSquare)
			? $this->imagickDraw->circle(
				($x + 0.5) * $this->scale,
				($y + 0.5) * $this->scale,
				($x + 0.5 + $this->options->circleRadius) * $this->scale,
				($y + 0.5) * $this->scale
			)
			: $this->imagickDraw->rectangle(
				$x * $this->scale,
				$y * $this->scale,
				($x + 1) * $this->scale,
				($y + 1) * $this->scale
			);
	}

}
