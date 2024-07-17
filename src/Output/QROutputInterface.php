<?php
/**
 * Interface QROutputInterface,
 *
 * @filesource   QROutputInterface.php
 * @created      02.12.2015
 * @package      chillerlan\QRCode\Output
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
	 * generates the output, optionally dumps it to a file, and returns it
	 *
	 * @return mixed
	 */
	public function dump(?string $file = null);

}
