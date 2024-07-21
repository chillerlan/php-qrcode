<?php
/**
 * Interface QROutputInterface,
 *
 * @created      02.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;

/**
 * Converts the data matrix into readable output
 */
interface QROutputInterface{

	/**
	 * Map of built-in output class FQN
	 *
	 * @var string[]
	 * @see https://github.com/chillerlan/php-qrcode/issues/223
	 */
	public const MODES = [
		QREps::class,
		QRFpdf::class,
		QRGdImageAVIF::class,
		QRGdImageBMP::class,
		QRGdImageGIF::class,
		QRGdImageJPEG::class,
		QRGdImagePNG::class,
		QRGdImageWEBP::class,
		QRImagick::class,
		QRInterventionImage::class,
		QRMarkupHTML::class,
		QRMarkupSVG::class,
		QRMarkupXML::class,
		QRStringJSON::class,
		QRStringText::class,
	];

	/**
	 * Map of module type => default value
	 *
	 * @var bool[]
	 */
	public const DEFAULT_MODULE_VALUES = [
		// light
		QRMatrix::M_NULL             => false,
		QRMatrix::M_DARKMODULE_LIGHT => false,
		QRMatrix::M_DATA             => false,
		QRMatrix::M_FINDER           => false,
		QRMatrix::M_SEPARATOR        => false,
		QRMatrix::M_ALIGNMENT        => false,
		QRMatrix::M_TIMING           => false,
		QRMatrix::M_FORMAT           => false,
		QRMatrix::M_VERSION          => false,
		QRMatrix::M_QUIETZONE        => false,
		QRMatrix::M_LOGO             => false,
		QRMatrix::M_FINDER_DOT_LIGHT => false,
		// dark
		QRMatrix::M_DARKMODULE       => true,
		QRMatrix::M_DATA_DARK        => true,
		QRMatrix::M_FINDER_DARK      => true,
		QRMatrix::M_SEPARATOR_DARK   => true,
		QRMatrix::M_ALIGNMENT_DARK   => true,
		QRMatrix::M_TIMING_DARK      => true,
		QRMatrix::M_FORMAT_DARK      => true,
		QRMatrix::M_VERSION_DARK     => true,
		QRMatrix::M_QUIETZONE_DARK   => true,
		QRMatrix::M_LOGO_DARK        => true,
		QRMatrix::M_FINDER_DOT       => true,
	];

	/**
	 * Map of module type => readable name (for CSS etc.)
	 *
	 * @var string[]
	 */
	public const LAYERNAMES = [
		// light
		QRMatrix::M_NULL             => 'null',
		QRMatrix::M_DARKMODULE_LIGHT => 'darkmodule-light',
		QRMatrix::M_DATA             => 'data',
		QRMatrix::M_FINDER           => 'finder',
		QRMatrix::M_SEPARATOR        => 'separator',
		QRMatrix::M_ALIGNMENT        => 'alignment',
		QRMatrix::M_TIMING           => 'timing',
		QRMatrix::M_FORMAT           => 'format',
		QRMatrix::M_VERSION          => 'version',
		QRMatrix::M_QUIETZONE        => 'quietzone',
		QRMatrix::M_LOGO             => 'logo',
		QRMatrix::M_FINDER_DOT_LIGHT => 'finder-dot-light',
		// dark
		QRMatrix::M_DARKMODULE       => 'darkmodule',
		QRMatrix::M_DATA_DARK        => 'data-dark',
		QRMatrix::M_FINDER_DARK      => 'finder-dark',
		QRMatrix::M_SEPARATOR_DARK   => 'separator-dark',
		QRMatrix::M_ALIGNMENT_DARK   => 'alignment-dark',
		QRMatrix::M_TIMING_DARK      => 'timing-dark',
		QRMatrix::M_FORMAT_DARK      => 'format-dark',
		QRMatrix::M_VERSION_DARK     => 'version-dark',
		QRMatrix::M_QUIETZONE_DARK   => 'quietzone-dark',
		QRMatrix::M_LOGO_DARK        => 'logo-dark',
		QRMatrix::M_FINDER_DOT       => 'finder-dot',
	];

	/**
	 * Note: do not call this constant from the interface, but rather from one of the child classes
	 *
	 * @var string
	 * @see \chillerlan\QRCode\Output\QROutputAbstract::toBase64DataURI()
	 */
	public const MIME_TYPE = '';

	/**
	 * Determines whether the given value is valid
	 */
	public static function moduleValueIsValid(mixed $value):bool;

	/**
	 * Generates the output, optionally dumps it to a file, and returns it
	 *
	 * please note that the value of QROptions::$cachefile is already evaluated at this point.
	 * if the output module is invoked manually, it has no effect at all.
	 * you need to supply the $file parameter here in that case (or handle the option value in your custom output module).
	 *
	 * @see \chillerlan\QRCode\QRCode::renderMatrix()
	 */
	public function dump(string|null $file = null):mixed;

}
