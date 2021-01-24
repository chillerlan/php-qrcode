<?php
/**
 * Class LuminanceSource
 *
 * @created      24.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */

namespace chillerlan\QRCode\Decoder;

use InvalidArgumentException;
use function chillerlan\QRCode\Common\arraycopy;

/**
 * The purpose of this class hierarchy is to abstract different bitmap implementations across
 * platforms into a standard interface for requesting greyscale luminance values. The interface
 * only provides immutable methods; therefore crop and rotation create copies. This is to ensure
 * that one Reader does not modify the original luminance source and leave it in an unknown state
 * for other Readers in the chain.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 */
abstract class LuminanceSource{

	protected array $luminances;
	protected int   $width;
	protected int   $height;

	public function __construct(int $width, int $height){
		$this->width  = $width;
		$this->height = $height;
		// In order to measure pure decoding speed, we convert the entire image to a greyscale array
		// up front, which is the same as the Y channel of the YUVLuminanceSource in the real app.
		$this->luminances = [];
		// @todo: grayscale?
		//$this->luminances = $this->grayScaleToBitmap($this->grayscale());
	}

	/**
	 * Fetches luminance data for the underlying bitmap. Values should be fetched using:
	 * {@code int luminance = array[y * width + x] & 0xff}
	 *
	 * @return array A row-major 2D array of luminance values. Do not use result.length as it may be
	 *         larger than width * height bytes on some platforms. Do not modify the contents
	 *         of the result.
	 */
	public function getMatrix():array{
		return $this->luminances;
	}

	/**
	 * @return int The width of the bitmap.
	 */
	public function getWidth():int{
		return $this->width;
	}

	/**
	 * @return int The height of the bitmap.
	 */
	public function getHeight():int{
		return $this->height;
	}

	/**
	 * Fetches one row of luminance data from the underlying platform's bitmap. Values range from
	 * 0 (black) to 255 (white). Because Java does not have an unsigned byte type, callers will have
	 * to bitwise and with 0xff for each value. It is preferable for implementations of this method
	 * to only fetch this row rather than the whole image, since no 2D Readers may be installed and
	 * getMatrix() may never be called.
	 *
	 * @param int $y  The row to fetch, which must be in [0,getHeight())
	 *
	 * @return array An array containing the luminance data.
	 */
	public function getRow(int $y):array{

		if($y < 0 || $y >= $this->getHeight()){
			throw new InvalidArgumentException('Requested row is outside the image: '.$y);
		}

		return arraycopy($this->luminances, $y * $this->width, [], 0, $this->width);
	}

	/**
	 * @param int $r
	 * @param int $g
	 * @param int $b
	 *
	 * @return void
	 */
	protected function setLuminancePixel(int $r, int $g, int $b):void{
		$this->luminances[] = $r === $g && $g === $b
			// Image is already greyscale, so pick any channel.
			? $r // (($r + 128) % 256) - 128;
			// Calculate luminance cheaply, favoring green.
			: ($r + 2 * $g + $b) / 4; // (((($r + 2 * $g + $b) / 4) + 128) % 256) - 128;
	}

}
