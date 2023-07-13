<?php
/**
 * Interface QROutputInterface,
 *
 * @created      02.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;

/**
 * Converts the data matrix into readable output
 */
interface QROutputInterface{

	/** @var string */
	public const MARKUP_HTML = 'html';
	/** @var string */
	public const MARKUP_SVG  = 'svg';
	/** @var string */
	public const GDIMAGE_PNG = 'png';
	/** @var string */
	public const GDIMAGE_JPG = 'jpg';
	/** @var string */
	public const GDIMAGE_GIF = 'gif';
	/** @var string */
	public const STRING_JSON = 'json';
	/** @var string */
	public const STRING_TEXT = 'text';
	/** @var string */
	public const IMAGICK     = 'imagick';
	/** @var string */
	public const FPDF        = 'fpdf';
	/** @var string */
	public const EPS         = 'eps';
	/** @var string */
	public const CUSTOM      = 'custom';

	/**
	 * Map of built-in output modes => class FQN
	 *
	 * @var string[]
	 */
	public const MODES = [
		self::MARKUP_SVG  => QRMarkupSVG::class,
		self::MARKUP_HTML => QRMarkupHTML::class,
		self::GDIMAGE_PNG => QRGdImage::class,
		self::GDIMAGE_GIF => QRGdImage::class,
		self::GDIMAGE_JPG => QRGdImage::class,
		self::STRING_JSON => QRString::class,
		self::STRING_TEXT => QRString::class,
		self::IMAGICK     => QRImagick::class,
		self::FPDF        => QRFpdf::class,
		self::EPS         => QREps::class,
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
		QRMatrix::M_TEST             => false,
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
		QRMatrix::M_TEST_DARK        => true,
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
		QRMatrix::M_TEST             => 'test',
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
		QRMatrix::M_TEST_DARK        => 'test-dark',
	];

	/**
	 * Determines whether the given value is valid
	 *
	 * @param mixed $value
	 */
	public static function moduleValueIsValid($value):bool;

	/**
	 * Generates the output, optionally dumps it to a file, and returns it
	 *
	 * please note that the value of QROptions::$cachefile is already evaluated at this point.
	 * if the output module is invoked manually, it has no effect at all.
	 * you need to supply the $file parameter here in that case (or handle the option value in your custom output module).
	 *
	 * @see \chillerlan\QRCode\QRCode::renderMatrix()
	 *
	 * @return mixed
	 */
	public function dump(string $file = null);

}
