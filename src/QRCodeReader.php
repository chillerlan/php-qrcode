<?php
/**
 * Class QRCodeReader
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\QRCode;

use chillerlan\Settings\SettingsContainerInterface;
use Imagick, InvalidArgumentException;
use chillerlan\QRCode\Decoder\{Decoder, DecoderResult, GDLuminanceSource, IMagickLuminanceSource};
use function extension_loaded, file_exists, file_get_contents, imagecreatefromstring, is_file, is_readable;

/**
 *
 */
final class QRCodeReader{

	/**
	 * The settings container
	 *
	 * @var \chillerlan\QRCode\QROptions|\chillerlan\Settings\SettingsContainerInterface
	 */
	private SettingsContainerInterface $options;

	/**
	 *
	 */
	public function __construct(SettingsContainerInterface $options = null){
		$this->options = $options ?? new QROptions;
	}

	/**
	 * @param \Imagick|\GdImage|resource $im
	 *
	 * @return \chillerlan\QRCode\Decoder\DecoderResult
	 */
	private function decode($im):DecoderResult{

		$source = $this->options->useImagickIfAvailable
			? new IMagickLuminanceSource($im)
			: new GDLuminanceSource($im);

		return (new Decoder)->decode($source);
	}

	/**
	 * @param string $imgFilePath
	 *
	 * @return \chillerlan\QRCode\Decoder\DecoderResult
	 */
	public function readFile(string $imgFilePath):DecoderResult{

		if(!file_exists($imgFilePath) || !is_file($imgFilePath) || !is_readable($imgFilePath)){
			throw new InvalidArgumentException('invalid file: '.$imgFilePath);
		}

		$im = $this->options->useImagickIfAvailable
			? new Imagick($imgFilePath)
			: imagecreatefromstring(file_get_contents($imgFilePath));

		return $this->decode($im);
	}

	/**
	 * @param string $imgBlob
	 *
	 * @return \chillerlan\QRCode\Decoder\DecoderResult
	 */
	public function readBlob(string $imgBlob):DecoderResult{

		if($this->options->useImagickIfAvailable){
			$im = new Imagick;
			$im->readImageBlob($imgBlob);
		}
		else{
			$im = imagecreatefromstring($imgBlob);
		}

		return $this->decode($im);
	}

	/**
	 * @param \Imagick|\GdImage|resource $imgSource
	 *
	 * @return \chillerlan\QRCode\Decoder\DecoderResult
	 */
	public function readResource($imgSource):DecoderResult{
		return $this->decode($imgSource);
	}

}
