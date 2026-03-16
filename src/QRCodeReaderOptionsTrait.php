<?php
/**
 * QRCodeReaderOptionsTrait.php
 *
 * @created      01.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode;

use function extension_loaded;

/**
 * Trait QRCodeReaderOptionsTrait
 */
trait QRCodeReaderOptionsTrait{

	/**
	 * Use Imagick (if available) when reading QR Codes,
	 * enables Imagick for the QR Code reader if the extension is available
	 */
	public bool $readerUseImagickIfAvailable = false {
		set{
			$this->readerUseImagickIfAvailable = ($value && extension_loaded('imagick'));
		}
	}

	/**
	 * Grayscale the image before reading
	 */
	public bool $readerGrayscale = false;

	/**
	 * Invert the colors of the image
	 */
	public bool $readerInvertColors = false;

	/**
	 * Increase the contrast before reading
	 *
	 * note that applying contrast works different in GD and Imagick, so mileage may vary
	 */
	public bool $readerIncreaseContrast = false;

}
