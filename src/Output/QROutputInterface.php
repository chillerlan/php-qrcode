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
	const MARKUP_HTML = 'html';
	/** @var string */
	const MARKUP_SVG  = 'svg';
	/** @var string */
	const GDIMAGE_PNG = 'png';
	/** @var string */
	const GDIMAGE_JPG = 'jpg';
	/** @var string */
	const GDIMAGE_GIF = 'gif';
	/** @var string */
	const STRING_JSON = 'json';
	/** @var string */
	const STRING_TEXT = 'text';
	/** @var string */
	const IMAGICK     = 'imagick';
	/** @var string */
	const FPDF        = 'fpdf';
	/** @var string */
	const EPS         = 'eps';
	/** @var string */
	const CUSTOM      = 'custom';

	/**
	 * Map of built-in output modes => modules
	 *
	 * @var string[]
	 */
	const MODES = [
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
	 * @var bool[]
	 */
	const DEFAULT_MODULE_VALUES = [
		// light
		QRMatrix::M_NULL                           => false,
		QRMatrix::M_DATA                           => false,
		QRMatrix::M_FINDER                         => false,
		QRMatrix::M_SEPARATOR                      => false,
		QRMatrix::M_ALIGNMENT                      => false,
		QRMatrix::M_TIMING                         => false,
		QRMatrix::M_FORMAT                         => false,
		QRMatrix::M_VERSION                        => false,
		QRMatrix::M_QUIETZONE                      => false,
		QRMatrix::M_LOGO                           => false,
		QRMatrix::M_TEST                           => false,
		// dark
		QRMatrix::M_DARKMODULE | QRMatrix::IS_DARK => true,
		QRMatrix::M_DATA | QRMatrix::IS_DARK       => true,
		QRMatrix::M_FINDER | QRMatrix::IS_DARK     => true,
		QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK  => true,
		QRMatrix::M_TIMING | QRMatrix::IS_DARK     => true,
		QRMatrix::M_FORMAT | QRMatrix::IS_DARK     => true,
		QRMatrix::M_VERSION | QRMatrix::IS_DARK    => true,
		QRMatrix::M_FINDER_DOT | QRMatrix::IS_DARK => true,
		QRMatrix::M_TEST | QRMatrix::IS_DARK       => true,
	];

	/**
	 * generates the output, optionally dumps it to a file, and returns it
	 *
	 * @return mixed
	 */
	public function dump(string $file = null);

}
