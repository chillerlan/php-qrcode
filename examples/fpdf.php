<?php
/**
 * @created      03.06.2020
 * @author       Maximilian Kresse
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__ . '/../vendor/autoload.php';

$options = new QROptions([
	'version'      => 7,
	'outputType'   => QRCode::OUTPUT_FPDF,
	'eccLevel'     => QRCode::ECC_L,
	'scale'        => 5,
	'imageBase64'  => false,
	'moduleValues' => [
		// finder
		QRMatrix::M_FINDER_DARK    => [0, 63, 255],    // dark (true)
		QRMatrix::M_FINDER_DOT     => [0, 63, 255],
		QRMatrix::M_FINDER         => [255, 255, 255], // light (false), white is the transparency color and is enabled by default
		// alignment
		QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
		QRMatrix::M_ALIGNMENT      => [255, 255, 255],
		// timing
		QRMatrix::M_TIMING_DARK    => [255, 0, 0],
		QRMatrix::M_TIMING         => [255, 255, 255],
		// format
		QRMatrix::M_FORMAT_DARK    => [67, 191, 84],
		QRMatrix::M_FORMAT         => [255, 255, 255],
		// version
		QRMatrix::M_VERSION_DARK   => [62, 174, 190],
		QRMatrix::M_VERSION        => [255, 255, 255],
		// data
		QRMatrix::M_DATA_DARK      => [0, 0, 0],
		QRMatrix::M_DATA           => [255, 255, 255],
		// darkmodule
		QRMatrix::M_DARKMODULE     => [0, 0, 0],
		// separator
		QRMatrix::M_SEPARATOR      => [255, 255, 255],
		// quietzone
		QRMatrix::M_QUIETZONE      => [255, 255, 255],
	],
]);


header('Content-type: application/pdf');

echo (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
