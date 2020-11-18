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

	const DEFAULT_MODULE_VALUES = [
		// light
		QRMatrix::M_DATA            => false, // 4
		QRMatrix::M_FINDER          => false, // 6
		QRMatrix::M_SEPARATOR       => false, // 8
		QRMatrix::M_ALIGNMENT       => false, // 10
		QRMatrix::M_TIMING          => false, // 12
		QRMatrix::M_FORMAT          => false, // 14
		QRMatrix::M_VERSION         => false, // 16
		QRMatrix::M_QUIETZONE       => false, // 18
		QRMatrix::M_LOGO            => false, // 20
		QRMatrix::M_TEST            => false, // 255
		// dark
		QRMatrix::M_DARKMODULE << 8 => true,  // 512
		QRMatrix::M_DATA << 8       => true,  // 1024
		QRMatrix::M_FINDER << 8     => true,  // 1536
		QRMatrix::M_ALIGNMENT << 8  => true,  // 2560
		QRMatrix::M_TIMING << 8     => true,  // 3072
		QRMatrix::M_FORMAT << 8     => true,  // 3584
		QRMatrix::M_VERSION << 8    => true,  // 4096
		QRMatrix::M_FINDER_DOT << 8 => true,  // 5632
		QRMatrix::M_TEST << 8       => true,  // 65280
	];

	/**
	 * generates the output, optionally dumps it to a file, and returns it
	 *
	 * @param string|null $file
	 *
	 * @return mixed
	 */
	public function dump(string $file = null);

}
