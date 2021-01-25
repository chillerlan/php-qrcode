<?php
/**
 * Class GDLuminanceSource
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode\Decoder;

use InvalidArgumentException;
use function get_resource_type, imagecolorat, imagecolorsforindex, imagesx, imagesy, is_resource;
use const PHP_MAJOR_VERSION;

/**
 * This class is used to help decode images from files which arrive as GD Resource
 * It does not support rotation.
 */
final class GDLuminanceSource extends LuminanceSource{

	/**
	 * @var resource|\GdImage
	 * @phan-suppress PhanUndeclaredTypeProperty
	 */
	private $gdImage;

	/**
	 * GDLuminanceSource constructor.
	 *
	 * @param resource|\GdImage $gdImage
	 * @phan-suppress PhanUndeclaredTypeParameter
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($gdImage){

		/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection, PhpFullyQualifiedNameUsageInspection */
		if(
			/** @phan-suppress-next-line PhanUndeclaredClassInstanceof */
			(PHP_MAJOR_VERSION >= 8 && !$gdImage instanceof \GdImage)
			|| (PHP_MAJOR_VERSION < 8 && (!is_resource($gdImage) || get_resource_type($gdImage) !== 'gd'))
		){
			throw new InvalidArgumentException('Invalid GD image source.');
		}

		parent::__construct(imagesx($gdImage), imagesy($gdImage));

		$this->gdImage = $gdImage;

		$this->setLuminancePixels();
	}

	private function setLuminancePixels():void{
		for($j = 0; $j < $this->height; $j++){
			for($i = 0; $i < $this->width; $i++){
				$argb  = imagecolorat($this->gdImage, $i, $j);
				$pixel = imagecolorsforindex($this->gdImage, $argb);

				$this->setLuminancePixel($pixel['red'], $pixel['green'], $pixel['blue']);
			}
		}
	}

}
