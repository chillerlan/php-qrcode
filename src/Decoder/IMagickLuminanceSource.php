<?php
/**
 * Class IMagickLuminanceSource
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

use Imagick, InvalidArgumentException;
use function count;

/**
 * This class is used to help decode images from files which arrive as Imagick Resource
 * It does not support rotation.
 */
final class IMagickLuminanceSource extends LuminanceSource{

	private Imagick $imagick;

	/**
	 * IMagickLuminanceSource constructor.
	 *
	 * @param \Imagick $imagick
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Imagick $imagick){
		parent::__construct($imagick->getImageWidth(), $imagick->getImageHeight());

		$this->imagick = $imagick;

		$this->setLuminancePixels();
	}

	private function setLuminancePixels():void{
		$this->imagick->setImageColorspace(Imagick::COLORSPACE_GRAY);
		$pixels = $this->imagick->exportImagePixels(1, 1, $this->width, $this->height, 'RGB', Imagick::PIXEL_CHAR);

		$countPixels = count($pixels);

		for($i = 0; $i < $countPixels; $i += 3){
			$this->setLuminancePixel($pixels[$i] & 0xff, $pixels[$i + 1] & 0xff, $pixels[$i + 2] & 0xff);
		}
	}

}
