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
