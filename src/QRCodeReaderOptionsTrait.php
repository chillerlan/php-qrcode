<?php
/**
 * QRCodeReaderOptionsTrait.php
 *
 * @created      01.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use function extension_loaded;

/**
 * Trait QRCodeReaderOptionsTrait
 */
trait QRCodeReaderOptionsTrait{

	/**
	 * Use Imagick (if available) when reading QR Codes
	 */
	protected bool $readerUseImagickIfAvailable = false;

	/**
	 * Grayscale the image before reading
	 */
	protected bool $readerGrayscale = false;

	/**
	 * Invert the colors of the image
	 */
	protected bool $readerInvertColors = false;

	/**
	 * Increase the contrast before reading
	 *
	 * note that applying contrast works different in GD and Imagick, so mileage may vary
	 */
	protected bool $readerIncreaseContrast = false;

	/**
	 * enables Imagick for the QR Code reader if the extension is available
	 */
	protected function set_readerUseImagickIfAvailable(bool $useImagickIfAvailable):void{
		$this->readerUseImagickIfAvailable = ($useImagickIfAvailable && extension_loaded('imagick'));
	}

}
