<?php
/**
 * @created      21.12.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Data\QRMatrix;

require_once __DIR__.'/../vendor/autoload.php';

$options = new QROptions([
	'version'      => 5,
	'outputType'   => QRCode::OUTPUT_STRING_TEXT,
	'eccLevel'     => QRCode::ECC_L,
	'moduleValues' => [
		// finder
		(QRMatrix::M_FINDER << 8)     => 'A', // dark (true)
		(QRMatrix::M_FINDER_DOT << 8) => 'A', // dark (true)
		QRMatrix::M_FINDER            => 'a', // light (false)
		// alignment
		(QRMatrix::M_ALIGNMENT << 8)  => 'B',
		QRMatrix::M_ALIGNMENT         => 'b',
		// timing
		(QRMatrix::M_TIMING << 8)     => 'C',
		QRMatrix::M_TIMING            => 'c',
		// format
		(QRMatrix::M_FORMAT << 8)     => 'D',
		QRMatrix::M_FORMAT            => 'd',
		// version
		(QRMatrix::M_VERSION << 8)    => 'E',
		QRMatrix::M_VERSION           => 'e',
		// data
		(QRMatrix::M_DATA << 8)       => 'F',
		QRMatrix::M_DATA              => 'f',
		// darkmodule
		(QRMatrix::M_DARKMODULE << 8) => 'G',
		// separator
		QRMatrix::M_SEPARATOR         => 'h',
		// quietzone
		QRMatrix::M_QUIETZONE         => 'x',
	],
]);

// <pre> to view it in a browser
$qrcode = (new QRCode($options))->render('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

echo '<pre style="line-height: 1;">'.$qrcode.'</pre>';
