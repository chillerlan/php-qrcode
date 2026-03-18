<?php
/**
 * Class LuminanceSourceAbstract
 *
 * @created      24.01.2021
 * @author       ZXing Authors
 * @author       Ashot Khanamiryan
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Common;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Decoder\QRCodeDecoderException;
use chillerlan\Settings\SettingsContainerInterface;
use function array_slice, file_exists, is_file, is_readable, realpath;

/**
 * The purpose of this class hierarchy is to abstract different bitmap implementations across
 * platforms into a standard interface for requesting greyscale luminance values.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 */
abstract class LuminanceSourceAbstract implements LuminanceSourceInterface{

	protected SettingsContainerInterface|QROptions $options;

	/**
	 * Fetches luminance data for the underlying bitmap. Values should be fetched using:
	 * `int luminance = array[y * width + x] & 0xff`
	 *
	 * A row-major 2D array of luminance values. Do not use result $length as it may be
	 * larger than $width * $height bytes on some platforms. Do not modify the contents
	 * of the result.
	 *
	 * @var int[]
	 */
	protected(set) array $luminances = [];

	/**
	 * The width of the bitmap.
	 */
	protected(set) int $width;

	/**
	 * The height of the bitmap.
	 */
	protected(set) int $height;

	public function __construct(int $width, int $height, SettingsContainerInterface|QROptions $options = new QROptions){
		$this->width   = $width;
		$this->height  = $height;
		$this->options = $options;
	}

	public function getRow(int $y):array{

		if($y < 0 || $y >= $this->height){
			throw new QRCodeDecoderException('Requested row is outside the image: '.$y);
		}

		return array_slice($this->luminances, ($y * $this->width), $this->width);
	}

	protected function setLuminancePixel(int $r, int $g, int $b):void{
		$this->luminances[] = ($r === $g && $g === $b)
			// Image is already greyscale, so pick any channel.
			? $r // (($r + 128) % 256) - 128;
			// Calculate luminance cheaply, favoring green.
			: (($r + 2 * $g + $b) / 4); // (((($r + 2 * $g + $b) / 4) + 128) % 256) - 128;
	}

	/**
	 * @throws \chillerlan\QRCode\Decoder\QRCodeDecoderException
	 */
	protected static function checkFile(string $path):string{
		$path = trim($path);

		if(!file_exists($path) || !is_file($path) || !is_readable($path)){
			throw new QRCodeDecoderException('invalid file: '.$path);
		}

		$realpath = realpath($path);

		if($realpath === false){
			throw new QRCodeDecoderException('unable to resolve path: '.$path);
		}

		return $realpath;
	}

}
